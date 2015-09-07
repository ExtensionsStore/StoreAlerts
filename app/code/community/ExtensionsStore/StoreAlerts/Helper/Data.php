<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isDebug()
    {
        $storeId = Mage::app()->getStore()->getId();
        $debugMode = Mage::getStoreConfig('extensions_store_storealerts/configuration/debug_mode', $storeId);
        
        return ($debugMode) ? true : false;
    }
    
    public function log($message, $level=null)
    {
        if ($this->isDebug()){
            
            Mage::log($message, $level, 'extensions_store_storealerts.log');
        }
    }
    
    /**
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function login($username, $password) {

    	$admin = Mage::getModel('admin/user');
    	$admin->login($username, $password);
        
    	return $admin;
    }    
    
    /**
     * Register alert for each subscriber
     *
     * @param string $type
     * @param string $message
     */
    public function saveAlert($type, $message)
    {
    	try {
    
    		$devices = Mage::getModel('storealerts/device')->getCollection();
    		$types = Mage::getModel('storealerts/alert');
    		$label = $types[$type]['label'];
    		$title = $types[$type]['title'];
    
    		foreach ($devices as $device){
    			 
    			$alertsStr = trim($device->getAlerts());
    			$selectedAlerts = explode(',', $alertsStr);
    			 
    			if (is_array($selectedAlerts) && in_array($type, $selectedAlerts)){
    
    				$alertIndex = array_search($type, $selectedAlerts);
    
    				$soundsStr = trim($device->getSounds());
    				$alertSounds = explode(',', $soundsStr);
    				$sound = (is_array($alertSounds) && count($alertSounds) == count($selectedAlerts)) ?
    				$alertSounds[$alertIndex] : 'default';
    				$userId = $device->getUserId();
    
    				$datetime = date('Y-m-d H:i:s');
    					
    				$alert = Mage::getModel('storealerts/alert');
    				$alert->setType($type);
    				$alert->setLabel($label);
    				$alert->setType($type);
    				$alert->setMessage($message);
    				$alert->setSound($sound);
    				$alert->setUserId($userId);
    				$alert->setSent(0);
    				$alert->setCreatedAt($datetime);
    				$alert->setUpdatedAt($datetime);
    				$alert->save();
    
    			}
    
    		}
    
    
    	} catch(Exception $e){
    
    		$this->log($e->getMessage());
    	}
    
    }    
    
}