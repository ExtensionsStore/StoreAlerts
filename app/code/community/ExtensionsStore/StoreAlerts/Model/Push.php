<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Push extends Mage_Core_Model_Abstract
{
    protected $_url = 'http://api.extensions-store.com/push';
    
    public function push($deviceToken, $accessToken, $message, $sound = null)
    {
        $data = array(
            'domain' => $_SERVER['HTTP_HOST'],
            'app' => 'Store Alerts',
            'device_token' => $deviceToken,
            'access_token' => $accessToken,
            'message' => $message,
            'sound' => $sound,
            );
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
                
        if (@$decoded->error === false){
            return $decoded;
        }
        
        return false;
        
    }    
    
    
}