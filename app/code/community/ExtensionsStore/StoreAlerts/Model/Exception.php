<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Exception extends Mage_Core_Model_Abstract
{
	const LIMIT = 10; //number of exception reports to log per cron schedule
	
	/**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('extensions_store_storealerts/exception');
    }	

}