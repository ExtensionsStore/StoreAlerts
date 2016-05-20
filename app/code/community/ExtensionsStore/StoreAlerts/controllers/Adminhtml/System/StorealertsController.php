<?php

/**
 * Store Alerts system config controller
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Adminhtml_System_StorealertsController 
	extends Mage_Adminhtml_Controller_Action
{
	
	public function testalertAction()
	{
    	$user = Mage::getSingleton('admin/session')->getUser();
    	$userId = $user->getUserId();
    	
    	$collection = Mage::getModel('storealerts/device')->getCollection();
    	$collection->addFieldToFilter('user_id', $userId);
    	$errorMessages = array();
    	
    	foreach ($collection as $device){
    		
    		$deviceId = $device->getId();
    		$device->load($deviceId);
    		
    		$deviceToken = $device->getDeviceToken();
    		$name = $device->getName();
    		$accessToken = $device->getAccessToken();
    		
    		if ($deviceToken && $accessToken){
    			$email = $user->getEmail();
    			$message = "Test from Store Alerts, device:  $deviceToken access: $accessToken";
    			$sound = 'default';
    			
    			$push = Mage::getSingleton('storealerts/push');
    			$result = $push->push($deviceToken, $accessToken, $email, $message, $sound);
    			
    			if (isset($result['error']) && $result['error']){
    			    
    			    if (isset($result['data']) && $result['data']) {
    			        Mage::getSingleton('adminhtml/session')->addError($result['data']);
    			    } else {
    			        $errorMessage = Mage::helper('storealerts')->__('Could not connect to %s %s.', $name, $deviceToken);
    			        Mage::getSingleton('adminhtml/session')->addError($errorMessage);
    			    }
    				 
    			} else {
    			    $message = Mage::helper('storealerts')->__('Test alert has been sent to your device %s %s.', $name, $deviceToken);
    			    Mage::getSingleton('adminhtml/session')->addSuccess($message);
    			}    			
    		}
    	}
    	 		 
		$this->_redirect('adminhtml/system_config/edit', array('section'=>'extensions_store_storealerts'));		
	}
	
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')
		->isAllowed('system/storealerts');
	}

}
