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
	    if (!$this->_validateFormKey()) {
	        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storealerts')->__('Unable to test alerts. Please try again later.'));
            return;
	    }	    
	    
	    $response = array();
	    $responseData = array();
	    $push = Mage::getSingleton('storealerts/push');
	    $user = Mage::getSingleton('admin/session')->getUser();
    	$email = $user->getEmail();
    	$userId = $user->getUserId();
    	$baseUrl = Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB);
    	$message = $this->getRequest()->getParam('message');
    	$message = ($message) ? $message : "Test from $baseUrl";
    	$preference = Mage::getModel('storealerts/preference')->load($userId);
    	$type = 'Test Alert';
    	$title = 'Test from '.$baseUrl;
    	$datetime = date('F j, Y g:i a',Mage::getModel('core/date')->timestamp(date('Y-m-d H:i:s')));
    	 
    	//send email test
    	$result = $push->pushEmailAlert($user, $type, $datetime, $title, $message);
    	if ($result){
    	    $responseData[] = "Email sent to $email";
    	}

    	//send slack test
    	if ($preference->getSlackHooks()){
    	    $slackHooks = explode(PHP_EOL,$preference->getSlackHooks());
            foreach ($slackHooks as $slackHookUrl){
                $result = $push->pushSlackHook(trim($slackHookUrl), $message);
                if ($result){
                    $responseData[] = "Slack hook sent to $slackHookUrl";
                }
            }
    	}    	
    	
    	//send device tests
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
    			$message = ($message) ? $message : "Test from Store Alerts, device:  $deviceToken access: $accessToken";
    			$sound = 'default';
    			
    			$result = $push->push($deviceToken, $accessToken, $email, $message, $sound);
    			
    			if (isset($result['error']) && $result['error']){
    			    
    			    if (isset($result['data']) && $result['data']) {
    			        $responseData[] = $result['data'];
    			    } else {
    			        $errorMessage = Mage::helper('storealerts')->__('Could not connect to %s %s.', $name, $deviceToken);
    			        $responseData[] = $errorMessage;
    			    }
    				 
    			} else {
    			    $responseData[] = Mage::helper('storealerts')->__('Test alert has been sent to your device %s %s.', $name, $deviceToken);;
    			}    			
    		}
    	}
    	 		 
		$this->getResponse()->clearHeaders()->setHeader ( 'Content-type', 'application/json', true )->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
	}
	
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')
		->isAllowed('system/storealerts');
	}

}
