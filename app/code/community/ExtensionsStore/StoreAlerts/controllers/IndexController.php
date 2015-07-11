<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_IndexController extends Mage_Core_Controller_Front_Action
{
    public function registerAction()
    {
        $result = array();
        
        if (Mage::helper('storealerts')->isDebug() || 
                Mage::app()->getStore()->isCurrentlySecure()){
            
            if ($data = $this->getRequest()->getPost()){
                
                $deviceToken = @$data['device_token'];
                $username = @$data['username'];
                $password = @$data['password'];
                
                if ($deviceToken && $username && $password){
                    
                    $registerModel = Mage::getSingleton('extensions_store_storealerts/register');
                    
                    $result = $registerModel->register($deviceToken, $username, $password);
                                        
                } else {
                    $result['error'] = true;
                    $result['data'] =  'No device token, username and/or password.';
                }
                
            } else {
                
                $result['error'] = true;
                $result['data'] =  'No admin login credentials posted.';
            }
        
        } else {
        
            $result['error'] = true;
            $result['data'] =  'Request is not over HTTPS.';
        }        
        
        if ($result['error'] === true){
            
            $errorMessage = $result['data'];
            Mage::helper('storealerts')->log($errorMessage);
        }
        
        $this->getResponse()->clearHeaders()
                ->setHeader('Content-type','application/json',true)
                ->setBody(Mage::helper('core')->jsonEncode($result));
        
    }
}