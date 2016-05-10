<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Push extends Mage_Core_Model_Abstract
{
    protected $_endPoint = '/alert';
    protected $_fp;
    protected $_curl;
    
    public function getCurl(){
    	if (!$this->curl){
    		$this->_fp = fopen('var/log/extensions_store_storealerts.log','w+');
    		$ch = curl_init();
    		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
    		curl_setopt($ch, CURLOPT_VERBOSE, true);
    		curl_setopt($ch, CURLOPT_STDERR, $this->_fp);
    		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    		$this->curl = $ch;
    	}
    	
    	return $this->curl;
    }
        
    public function push($deviceToken, $accessToken, $email, $message, $sound = 'default')
    {
    	$helper = Mage::helper('storealerts');
    	$result = array();
        $data = array(
            'domain' => $helper->getDomain(),
            'app' => 'Store Alerts',
            'device_token' => $deviceToken,
            'access_token' => $accessToken,
            'message' => $message,
            'sound' => $sound,
        	'email' => $email,
            );
        $dataStr = json_encode($data);
        
    	$ch = $this->getCurl();
        $headers = array(
            'Host: '.$helper->getApiHost(),
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataStr),
            );
        
        $url = $helper->getApiUrl().$this->_endPoint;
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                                                                        //proceeding with the login.
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);

        $response = curl_exec($ch);
        
        $decoded = json_decode($response, true);
                
        if ($decoded && isset($decoded['error'])){
            return $decoded;
        }
        
        $result['error'] = true;
        $result['data'] = curl_error($ch);
        
        return $result;
        
    }    
    
    public function pushSlackHook($slackHookUrl, $message){
    	
    	$dataStr = json_encode(array('text'=>$message));
    	
    	$headers = array(
    			'Host: hooks.slack.com',
    			'Content-Type: application/json',
    			'Content-Length: ' . strlen($dataStr),
    	);
    	
    	$ch = $this->getCurl();
    		
    	curl_setopt($ch, CURLOPT_URL, $slackHookUrl);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                                                                        //proceeding with the login.
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
    	
    	$response = curl_exec($ch);    	
    	
    	return $response;
    	 
    }
    
}