<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Resource_Preference 
    extends Mage_Core_Model_Resource_Db_Abstract
{
	
    protected function _construct()
    {
        $this->_init('extensions_store_storealerts/preference', 'user_id');
    	$this->_isPkAutoIncrement = false;
    }
	
}
