<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Model_Register extends Mage_Core_Model_Abstract {

    protected $_admin;
    protected $_endPoint = '/account';

    /**
     * 
     * @param string $deviceToken
     * @param string $username
     * @param string $password
     * @param string $registerToken
     * @return array
     */
    public function register($deviceToken, $name, $username, $password, $accessToken = "") {
        
        $result = array();

        try {

            $result = $this->_login($username, $password);

            if ($result['error'] === false) {

                $result = $this->_registerDeviceToken($deviceToken, $accessToken, $name);

                if ($result['error'] === false) {
                    
                    $accessToken = $result['data'];
                    
                    if ($accessToken){
                        
                        $result = $this->_registerAdmin($deviceToken, $accessToken, $name);
                        
                        if ($result['error'] === false){
                            
                            $result['data'] = array(
                                'access_token' => $accessToken,
                            );                             
                        } 
                        
                    } else {
                        
                        $result['data'] = 'Registration token will be sent to device.';
                    }
                    
                }
            }
            
        } catch (Exception $ex) {

            $message = $ex->getMessage();
            $result['error'] = true;
            $result['data'] = $message;
            Mage::helper('storealerts')->log($message);
        }

        return $result;
    }

    /**
     * 
     * @param string $username
     * @param string $password
     * @return array
     */
    protected function _login($username, $password) {
        $result = array();

        $admin = Mage::helper('storealerts')->login($username, $password);

        if ($admin->getId()) {
            
            $this->_admin = $admin;
            $result['error'] = false;
            $result['data'] = $admin->getId();

        } else {
            
            $result['error'] = true;
            $result['data'] = 'Could not login admin.';
        }

        return $result;
    }

    /**
     * 
     * @param string $deviceToken
     * @param string $accessToken
     * @return array
     */
    protected function _registerDeviceToken($deviceToken, $accessToken = null, $name = null) {
        
        $result = array();
        $helper = Mage::helper('storealerts');
        
        $data = array('email' => $this->_admin->getEmail(),
            'firstname' => $this->_admin->getFirstname(),
            'lastname' => $this->_admin->getLastname(),
            'domain' => $helper->getDomain(),
            'app' => 'Store Alerts',
            'device_token' => $deviceToken,
            'access_token' => ($accessToken) ? $accessToken : '' ,
        	'name' => ($name) ? $name : '',
        );
        $dataStr = json_encode($data);

        $ch = curl_init();
        $headers = array(
            'Host: '.$helper->getApiHost(),
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataStr),
        );
        
        $url = $helper->getApiUrl().$this->_endPoint;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                                                                        //proceeding with the login.
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ch);

        curl_close($ch);
        
        if ($response){
            
            $result = json_decode($response, true);

            if (isset($result['error']) && isset($result['data'])) {
            	
            	$device = Mage::getModel('extensions_store_storealerts/device');
            	$device->load($deviceToken, 'device_token');
            	$datetime = date('Y-m-d H:i:s');
            	
            	if (!$device->getId()) {
            	
            		$device->setCreatedAt($datetime);
            	}
            	
                if ($name){
            		$device->setName($name);
            	}
            	$device->setDeviceToken($deviceToken);
            	$device->setAccessToken($accessToken);
            	$device->setUserId($this->_admin->getId());
            	$device->setUpdatedAt($datetime);
            	
            	$device->save();                
                return $result;
                
            } else {
                
                $result['error'] = true;
                $result['data'] = $response;
            }
            
        } else {
            
            $result['error'] = true;
            $result['data'] = 'No response.';
        }

        return $result;
    }
    
    /**
     * 
     * @param string $deviceToken
     * @param string $accessToken
     * @param string $name
     * @return array
     */
    protected function _registerAdmin($deviceToken, $accessToken, $name = null)
    {
        $result = array();
        
        try {
            
            $device = Mage::getModel('extensions_store_storealerts/device');
            $device->load($deviceToken, 'device_token');
            $datetime = date('Y-m-d H:i:s');

            if (!$device->getId()) {

                $device->setCreatedAt($datetime);
            }

            if ($name){
            	$device->setName($name);
            }
            $device->setDeviceToken($deviceToken);
            $device->setAccessToken($accessToken);
            $device->setUserId($this->_admin->getId());
            $device->setUpdatedAt($datetime);

            $device->save();  
            $result['error'] = false;
            $result['data'] = $device->getId();

        } catch (Exception $ex) {

            $message = $ex->getMessage();
            $result['error'] = true;
            $result['data'] = $message;
            Mage::helper('storealerts')->log($message);
        } 
        
        return $result;
     
    }

}
