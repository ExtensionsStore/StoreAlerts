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
    	$devices = Mage::getModel('storealerts/device')->getCollection();
    	$push = Mage::getSingleton('storealerts/push');
    	 
    	foreach ($devices as $device){
    		
    		$device->load($device->getId());
    		$deviceToken = $device->getDeviceToken();
    		$accessToken = $device->getAccessToken();
    		$userId = $device->getUserId();
    		$adminUser = Mage::getModel('admin/user')->load($userId);
    		$email = $adminUser->getEmail();
    		
    		$alerts = Mage::getModel('storealerts/alert')->getCollection();
    		$alerts->addFieldToFilter('sent',0);
    		$alerts->addFieldToFilter('user_id',$userId);
    		
    		foreach ($alerts as $alert){
    			
    			$alert->load($alert->getId());
    			$message = $alert->getMessage();
    			$sound = $alert->getSound();
    		
    			$result = $push->push($deviceToken, $accessToken, $email, $message, $sound);
    			
    			if ($result){
    				$numPushed++;
    				$datetime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
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
    	$today = date("Y-m-d", Mage::getModel('core/date')->timestamp(time()));
    	$notifications->addFieldToFilter('date_added',array('gteq' => $today));
    	
    	//$selectStr = (string)$notifications->getSelect();
    	
    	if ($notifications->getSize()>0){
    		
    		$helper = Mage::helper('storealerts');
    		
    		foreach ($notifications as $notification){
    				
    			$message = $notification->getTitle();
    				
    			$helper->saveAlert($notificationCode, $message);
    			
    			if ($markNotificationRead){
    				$notification->setIsRead(1)->save();
    			}
    			
    			$numNotifications++;
    		} 		
    		
    	}
    	     	 
    	return 'Number of notifications submitted: '.$numNotifications;    	
    }
    
    
}