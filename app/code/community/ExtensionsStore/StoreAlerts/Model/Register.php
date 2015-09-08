<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Model_Register extends Mage_Core_Model_Abstract {

    protected $_admin;
    protected $_url = 'http://api.extensions-store.com/register';

    /**
     * 
     * @param string $deviceToken
     * @param string $username
     * @param string $password
     * @param string $registerToken
     * @return array
     */
    public function register($deviceToken, $username, $password, $accessToken = "") {
        
        $result = array();

        try {

            $result = $this->_login($username, $password);

            if ($result['error'] === false) {

                $result = $this->_registerDeviceToken($deviceToken, $accessToken);

                if ($result['error'] === false) {
                    
                    $accessToken = $result['data'];
                    
                    if ($accessToken){
                        
                        $result = $this->_registerAdmin($deviceToken, $accessToken);
                        
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
    protected function _registerDeviceToken($deviceToken, $accessToken = null) {
        
        $result = array();
        
        $data = array('email' => $this->_admin->getEmail(),
            'firstname' => $this->_admin->getFirstname(),
            'lastname' => $this->_admin->getLastname(),
            'domain' => $_SERVER['HTTP_HOST'],
            'app' => 'Store Alerts',
            'device_token' => $deviceToken,
            'access_token' => $accessToken,
        );
        $dataStr = json_encode($data);

        $ch = curl_init();
        $fp = fopen('var/log/extensions_store_storealerts.log', 'w+');
        $headers = array(
            'Host: api.extensions-store.com',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataStr),
        );

        curl_setopt($ch, CURLOPT_URL, $this->_url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $fp);
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
     * @return array
     */
    protected function _registerAdmin($deviceToken, $accessToken)
    {
        $result = array();
        
        try {
            
            $device = Mage::getModel('extensions_store_storealerts/device');
            $device->load($this->_admin->getId(), 'user_id');
            $datetime = date('Y-m-d H:i:s');

            if (!$device->getId()) {

                $device->setCreatedAt($datetime);
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
