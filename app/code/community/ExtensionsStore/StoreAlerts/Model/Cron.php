<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Cron
{
    
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
    				$datetime = date('Y-m-d H:i:s');
    				$alert->setSent(1)->setUpdatedAt($datetime)->save();
    			}
    		}    		
    		
    	}
    	
        return 'Number of alerts pushed: '.$numPushed;
        
    }    
    
    
}