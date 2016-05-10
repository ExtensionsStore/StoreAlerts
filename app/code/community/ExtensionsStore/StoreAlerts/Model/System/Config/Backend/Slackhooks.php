<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_System_Config_Backend_Slackhooks 
    extends Mage_Core_Model_Config_Data 
{

    /**
     * Set admin alert hooks
     *
     * @return ExtensionsStore_StoreAlerts_Model_System_Config_Backend_Slackhooks
     */
    protected function _afterSave() {
    	
        try {
        	
        	$adminUser = Mage::getSingleton('admin/session')->getUser();
        	$preference = Mage::getModel('extensions_store_storealerts/preference');
        	$preference->load($adminUser->getId());
        	
        	$slackHooks = $this->getValue();
        	
        	$datetime = date('Y-m-d H:i:s');
        	 
        	$preference->setSlackHooks($slackHooks);
        	$preference->setUpdatedAt($datetime);
        	$preference->save();
        	 
        } catch(Exception $e){
        	$message = $e->getMessage();
        	Mage::helper('storealerts')->log($message);        	
        }
        
        return $this;
        
    }

}
