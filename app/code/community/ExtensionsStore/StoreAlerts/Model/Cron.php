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
    	$alerts->addFieldToFilter('updated_at',array('gteq' => date('Y-m-d')));
    	
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
    	
    	//$selectStr = (string)$notifications->getSelect();
    	
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
    
    
}