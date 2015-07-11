<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Register extends Mage_Core_Model_Abstract
{
    
    protected $_admin;
    protected $_url = 'http://api.extensions-store.com/register';
    
    /**
     * 
     * @param string $deviceToken
     * @param string $username
     * @param string $password
     * @return array
     */
    public function register($deviceToken, $username, $password)
    {
        $result = array();
        
        try {
            
            if ($this->_login($username, $password)){
                
                if ($res = $this->_registerDeviceToken($deviceToken)){
                    
                    $consumer = Mage::getModel('oauth/consumer');
                    $consumer->load('Store Alerts', 'name');

                    $result['error'] = false;
                    $result['data'] = array(
                            'confirmed' => $res['data'],
                            'key' => $consumer->getKey(),
                            'secret' => $consumer->getSecret(),
                            'callback_url' => $consumer->getCallbackUrl()
                        );    
                    
                } else {
                    
                    $result['error'] = true;                    
                    $result['data'] = 'Could not register device '. $deviceToken;
                }
                                
            } else {
                
                $result['error'] = true;
                $result['data'] = 'Could not login admin.';
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
     * @param string $deviceToken
     * @param string $username
     * @param string $password
     * @return boolean
     */
    protected function _login($username, $password)
    {
        $admin = Mage::getModel('admin/user');
        $admin->login($username, $password);     
        
        if ($admin->getId()) {
            
            $this->_admin = $admin;

            return true;
        }

        return false;
    }
    
    /**
     * 
     * @param string $deviceToken
     * @return boolean
     */
    protected function _registerDeviceToken($deviceToken)
    {
        $data = array('email'=> $this->_admin->getEmail(),
            'firstname' => $this->_admin->getFirstname(),
            'lastname' => $this->_admin->getLastname(),
            'app' => 'Store Alerts',
            'device_token' => $deviceToken);
        $dataStr = json_encode($data);
        
        $ch = curl_init();
        $fp = fopen('var/log/extensions_store_storealerts.log','w+');
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
        
        $decoded = json_decode($response);
        
        if ($decoded->error === false){
            return true;
        }
        
        return false;
        
    }
    
}