<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_IndexController extends Mage_Core_Controller_Front_Action {
	
	protected function _checkPost($requireAccessToken = true)
	{
		if (Mage::helper ( 'storealerts' )->isDebug () || Mage::app ()->getStore()->isCurrentlySecure ()) {
				
			if ($data = $this->getRequest ()->getPost()) {
				
				$dataObj = new Varien_Object();
				$dataObj->setData($data);
		
				$deviceToken = $dataObj->getDeviceToken();
				$username = $dataObj->getUsername();
				$password = $dataObj->getPassword();
				$accessToken = ($requireAccessToken) ? $dataObj->getAccessToken() : true;
		
				if ($deviceToken && $username && $password && $accessToken) {
					
					$admin = Mage::helper('storealerts')->login($username, $password);
					
					if ($admin->getId()){
						
						try {
							
							$datetime = date("Y-m-d H:i:s");
							
							$preference = Mage::getModel ( 'extensions_store_storealerts/preference' );
							$preference->load($admin->getId());
							
							if (!$preference->getId()){
								$preference->setUserId($admin->getId())
								->setCreatedAt($datetime)
								->setUpdatedAt($datetime)
								->save();
							}
							
							$dataObj->setPreference($preference);
							
							$device = Mage::getModel ( 'extensions_store_storealerts/device' );
							$device->load ($deviceToken, 'device_token');
							
							if (!$device->getId()){
								
								$device->setDeviceToken($deviceToken)
									->setUserId($admin->getId())
									->setCreatedAt($datetime)
									->setUpdatedAt($datetime)
									->save();
							}
														
							$dataObj->setDevice($device);
							
							$result['error'] = false;
							$result['data'] = $dataObj;
						
						} catch (Exception $e){
							
							$result ['error'] = true;
							$result ['data'] = $e->getMessage();
						}							
						
					} else {
						
						$result['error'] = true;
						$result['data'] = 'Could not login admin';
					}
						
				} else {
					
					$result ['error'] = true;
					$result ['data'] = 'No device token, username, password or access token.';
				}
			} else {
		
				$result ['error'] = true;
				$result ['data'] = 'No admin login credentials posted.';
			}
		} else {
				
			$result ['error'] = true;
			$result ['data'] = 'Request is not over HTTPS.';
		}
		
		if ($result ['error'] === true) {
				
			$errorMessage = $result ['data'];
			Mage::helper ( 'storealerts' )->log ( $errorMessage );
		}
		
		return $result;
	}
	
	/**
	 * Register admin's device
	 */
	public function registerAction() {
		
		$result = array();
		
		$result = $this->_checkPost(false);
		
		if ($result['error'] === false){
			
			$registerModel = Mage::getSingleton ( 'extensions_store_storealerts/register' );
			$dataObj = $result['data'];
				
			$result = $registerModel->register($dataObj->getDeviceToken(), 
					$dataObj->getUsername(), 
					$dataObj->getPassword(), 
					$dataObj->getAccessToken());
		}
		
		$this->getResponse()->clearHeaders()->setHeader ( 'Content-type', 'application/json', true )->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
	}
	
	/**
	 * Get admin's alert preferences
	 */
	public function preferencesAction() 
	{
		$result = array();
		
		$result = $this->_checkPost();
		
		if ($result['error'] === false){
				
			$dataObj = $result['data'];
			$preference = $dataObj->getPreference();			
			$result = $preference->getPreferences();
		}
		
		$this->getResponse()->clearHeaders()->setHeader ( 'Content-type', 'application/json', true )->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
	}
	
	public function updatePreferencesAction()
	{
		$result = array();
		
		$result = $this->_checkPost();
		
		if ($result['error'] === false){
		
			$dataObj = $result['data'];
			$preference = $dataObj->getPreference();			
			
			$result = $preference->updatePreferences($dataObj);
		}
		
		$this->getResponse()->clearHeaders()->setHeader ( 'Content-type', 'application/json', true )->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
	}
	
	/**
	 * Get admin's device alerts
	 */
	public function alertsAction() {
		
		$result = array();
		
		$result = $this->_checkPost();
		
		if ($result['error'] === false){
				
			$dataObj = $result['data'];
			$device = $dataObj->getDevice();
			$page = (int)$this->getRequest()->getParam('page');
			$limit = (int)$this->getRequest()->getParam('limit');
			$result = $device->getAlertsArray($page, $limit);
				
		}
		
		$this->getResponse()->clearHeaders()->setHeader ( 'Content-type', 'application/json', true )->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
	}
	
	/**
	 * 
	 */
	public function deleteAlertAction()
	{
		$result = array();
		
		$result = $this->_checkPost();
		
		if ($result['error'] === false){
		
			$dataObj = $result['data'];
			$alertId = $dataObj->getAlertId();
			$device = $dataObj->getDevice();
			$result = $device->deleteAlert($alertId);
		
		}
		
		$this->getResponse()->clearHeaders()->setHeader ( 'Content-type', 'application/json', true )->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
	}
	
}
