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
    	
    	$helper = Mage::helper('storealerts');
    	$alerts = Mage::getModel('storealerts/alert')->getCollection();
    	$alerts->addFieldToFilter('sent',0);
    	$updatedAt = date('Y-m-d H:i:s', strtotime('-1 hour')); //only push alerts generated in last hour
    	$alerts->addFieldToFilter('updated_at',array('gteq' => $updatedAt));
    	
    	if ($alerts->getSize()>0){
    		    		
    		$admins = array();
    		$preferences = array();
    		
    		foreach ($alerts as $alert){
    		
    			$userId = $alert->getUserId();
    			if (!isset($admins[$userId])){
    				$adminUser = Mage::getModel('admin/user')->load($userId);
    				$admins[$userId] = $adminUser;
    			} else {
    				$adminUser = $admins[$userId];
    			}
    			
    			if ($adminUser && $adminUser->getId() && $adminUser->getIsActive()){

    				$type = $alert->getType();
    				$label = $alert->getLabel();
    				$title = $alert->getTitle();
    				$message = $alert->getMessage();
    				$datetime = date('F j, Y g:i a',Mage::getModel('core/date')->timestamp($alert->getUpdatedAt()));
    				
    				//load preferences for this admin
    				if (!isset($preferences[$userId])){
    					$preference = Mage::getModel('storealerts/preference')->load($userId);
    					if ($preference->getSlackHooks()){
    						$slackHooks = explode(PHP_EOL,$preference->getSlackHooks());
    						$slackHooks = array_filter($slackHooks);
    						if (is_array($slackHooks) && count($slackHooks)>0 && $slackHooks[0]){
    							$slackHooksUrls = array();
    							$selectedAlerts = explode(',',$preference->getAlerts());
    							foreach($selectedAlerts as $i=>$selectedAlert){
    								$index = (count($slackHooks) == 1) ? 0 : $i;
    								$slackHooksUrls[$selectedAlert] = $slackHooks[$index];
    							}
    							$preference->setSlackHookUrls($slackHooksUrls);
    						}
    					}
    					$preferences[$userId] = $preference;
    						
    				}else {
    					$preference = $preferences[$userId] ;
    				}
    				
    				//send email alert
    				if ($preference->getEmailAlerts()){
    					$result = $push->pushEmailAlert($adminUser, $label, $datetime, $title, $message);
    					if (!$result){
    					    $logTitle = "Store Alerts could not send alert to email {$adminUser->getEmail()}";
    					    $logMessage = "Alert ID: {$alert->getId()}; Alert title: $title";
    					    $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $logMessage, $logTitle, null);
    					}    					
    				}
    				
    				//send slack hooks
    				if ($preference->getSlackHookUrls()){
    					
    					$slackHookUrls = $preference->getSlackHookUrls();
    					$slackHookUrl = $slackHookUrls[$type];
    					$result = $push->pushSlackHook(trim($slackHookUrl), $message);
    					if (!$result){
    					    $logTitle = "Store Alerts could not push to slack hook: {$slackHookUrl}";
    					    $logMessage = "Alert ID: {$alert->getId()}; Alert title: $title";
    					    $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $logMessage, $logTitle, null);
    					}
    				}
    				
    				//send device alert
    				$sound = $alert->getSound();
    				$devices = Mage::getModel('storealerts/device')->getCollection();
    				$devices->addFieldToFilter('user_id',$userId);
    				
    				foreach ($devices as $device){
    					$deviceToken = $device->getDeviceToken();
    					$accessToken = $device->getAccessToken();
    					if ($deviceToken && $accessToken){
    						$result = $push->push($deviceToken, $accessToken, $email, substr($message, 0, 1900), $sound);
    						if (!$result){
    						    $logTitle = "Store Alerts could not push to device: {$deviceToken}";
    						    $logMessage = "Alert ID: {$alert->getId()}; Alert title: $title";
    						    $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $logMessage, $logTitle, null);
    						}
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
    	$preferences = Mage::getModel('storealerts/preference')->getCollection();
    	$preferences->addFieldToFilter('alerts', array('like' => '%exception%'));
    	 
    	if ($preferences->getSize()>0){
    		$limit = ExtensionsStore_StoreAlerts_Model_Exception::LIMIT;
    		$counter = 0;
    		$reportDir = Mage::getBaseDir().DS.'var'.DS.'report';
    		chmod($reportDir.DS.'*',0664);
    		$files = scandir($reportDir);
    		$helper = Mage::helper('storealerts');
    		$currentDate = date('Y-m-d');
    		 
			if (count($files)>0){
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
							$exception = Mage::getModel('extensions_store_storealerts/exception')->load($file,'file');
							if (!$exception->getId()){
								$exception->setCreatedAt($datetime);
							}
							$exception->setFile($file)
							->setContent($content)
							->setUpdatedAt($datetime)
							->save();
							if ($exception->getId()){
								if ($exceptionTime > time()-3600){
									$reportAr = unserialize($content);
									$title = $reportAr[0];
									$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::EXCEPTION, $content, $title, $datetime);
								}
									
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
			}
    	}
    	
    	return 'Number of exeptions logged: '.$numExceptions;
    }
}