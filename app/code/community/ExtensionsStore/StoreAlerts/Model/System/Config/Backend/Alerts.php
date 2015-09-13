<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_System_Config_Backend_Alerts 
    extends Mage_Core_Model_Config_Data 
{

    /**
     * Set admin alerts
     *
     * @return ExtensionsStore_StoreAlerts_Model_System_Config_Backend_Alerts
     */
    protected function _afterSave() {
    	
        try {
        	
        	$adminUser = Mage::getSingleton('admin/session')->getUser();
        	$device = Mage::getModel('extensions_store_storealerts/device');
        	$device->load($adminUser->getId(), 'user_id');
        	
        	$alerts = $this->getValue();
        	$datetime = date('Y-m-d H:i:s');
        	 
        	$device->setAlerts($alerts);
        	$device->setUpdatedAt($datetime);
        	$device->save();
        	 
        } catch(Exception $e){
        	$message = $e->getMessage();
        	Mage::helper('storealerts')->log($message);        	
        }
        
        return $this;
        
    }

}
