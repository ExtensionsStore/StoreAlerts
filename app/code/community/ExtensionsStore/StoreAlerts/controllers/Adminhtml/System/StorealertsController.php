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
    	$device = Mage::getModel('storealerts/device');
    	$user = Mage::getSingleton('admin/session')->getUser();
    	$userId = $user->getUserId();
    	
    	$device->load($userId, 'user_id');
    	$deviceToken = $device->getDeviceToken();
    	$accessToken = $device->getAccessToken();
    	$email = $user->getEmail();    	
    	$message = "Test from Store Alerts";
    	$sound = 'default';
		
    	$push = Mage::getSingleton('storealerts/push');
    	$result = $push->push($deviceToken, $accessToken, $email, $message, $sound);
    	 
		if (!$result['error']){
			
			$message = Mage::helper('storealerts')->__('Test alert has been sent to your device.');
		     
		    Mage::getSingleton('adminhtml/session')->addSuccess($message);
		     
		} else {
		
		    Mage::getSingleton('adminhtml/session')->addError($result['data']);
		}
		 
		$this->_redirect('adminhtml/system_config/edit', array('section'=>'extensions_store_storealerts'));		
	}
	


}
