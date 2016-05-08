<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Cron
{
    /**
     * 
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function pushAlerts($schedule)
    {
		$numPushed = 0;
    	$push = Mage::getSingleton('storealerts/push');
    	
    	$alerts = Mage::getModel('storealerts/alert')->getCollection();
    	$alerts->addFieldToFilter('sent',0);
    	$updatedAt = date('Y-m-d H:i:s', strtotime('-1 hour')); //only push alerts generated in last hour
    	$alerts->addFieldToFilter('updated_at',array('gteq' => $updatedAt));
    	
    	if ($alerts->getSize()>0){
    		
    		foreach ($alerts as $alert){
    		
    			$userId = $alert->getUserId();
    			$adminUser = Mage::getModel('admin/user')->load($userId);
    			
    			if ($adminUser->getId() && $adminUser->getIsActive()){
    				
    				$email = $adminUser->getEmail();
    				$message = $alert->getMessage();
    				$sound = $alert->getSound();
    				$devices = Mage::getModel('storealerts/device')->getCollection();
    				$devices->addFieldToFilter('user_id',$userId);
    				
    				foreach ($devices as $device){
    					$deviceToken = $device->getDeviceToken();
    					$accessToken = $device->getAccessToken();
    					if ($deviceToken && $accessToken){
    						$result = $push->push($deviceToken, $accessToken, $email, $message, $sound);
    					}
    				}
    				
    				$numPushed++;
    				$datetime = date("Y-m-d H:i:s");
    				$alert->setSent(1)->setUpdatedAt($datetime)->save();
    			}
    		}    		
    	}
    	
        return 'Number of alerts pushed: '.$numPushed;
        
    }    
    
    /**
     * 
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function checkNotifications($schedule)
    {
    	$numNotifications = 0;
    	$severity = Mage::getStoreConfig('extensions_store_storealerts/configuration/notification_severity');
    	$markNotificationRead = Mage::getStoreConfig('extensions_store_storealerts/configuration/mark_notification_read');
    	 
    	$notifications = Mage::getModel('adminnotification/inbox')->getCollection();
    	$notifications->addFieldToFilter('is_read',0);
    	$notifications->addFieldToFilter('is_remove',0);
    	$notifications->addFieldToFilter('severity',array('lteq' => $severity));
    	$today = date("Y-m-d");
    	$notifications->addFieldToFilter('date_added',array('gteq' => $today));
    	    	
    	if ($notifications->getSize()>0){
    		
    		$helper = Mage::helper('storealerts');
    		
    		foreach ($notifications as $notification){
    				
    			$message = $notification->getTitle();
    				
    			$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::NOTIFICATION, $message);
    			
    			if ($markNotificationRead){
    				$notification->setIsRead(1)->save();
    			}
    			
    			$numNotifications++;
    		} 		
    		
    	}
    	     	 
    	return 'Number of notifications submitted: '.$numNotifications;    	
    }
    
    /**
     * Store exception reports in exception table
     * Save report in alert 
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function logExceptions($schedule){
    	
    	$numExceptions = 0;
    	$limit = ExtensionsStore_StoreAlerts_Model_Exception::LIMIT;
    	$counter = 0;
    	$reportDir = 'var'.DS.'report';
    	$files = scandir($reportDir);
    	$helper = Mage::helper('storealerts');
    	$currentDate = date('Y-m-d');
    	
    	foreach ($files as $file){
    		try {
    			$fileToLog = $reportDir.DS.$file;
    			if (is_file($fileToLog)){
    				if ($counter >= $limit){
    					break;
    				}
    				$counter++;
    				$content = file_get_contents($fileToLog);
    				$exceptionTime = filemtime($fileToLog);
    				$datetime = date('Y-m-d H:i:s', $exceptionTime);
    				$exception = Mage::getModel('extensions_store_storealerts/exception');
    				$exception->setFile($file)
    					->setContent($content)
    					->setCreatedAt($datetime)
    					->setUpdatedAt($datetime)
    					->save();
    				if ($exception->getId()){

    					$reportAr = unserialize($content);
    					$title = $reportAr[0];
    					$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::EXCEPTION, $content, $title, $datetime);
    					
    					$loggedDir = $reportDir.DS.'logged';
    					if (!is_dir($loggedDir)){
    						mkdir($loggedDir);
							chmod($loggedDir,0775); 						
    					}
    					$loggedFile = $loggedDir.DS.$file;
    					rename($fileToLog, $loggedFile);
    					$numExceptions++;
    				}
    			}
    		}catch(Exception $e){
    			Mage::log($e->getMessage(),Zend_Log::ERR,'extensions_store_storealerts.log');
    		}
    	}
    	
    	return 'Number of exeptions logged: '.$numExceptions;
    }
}