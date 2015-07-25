<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Alert extends Mage_Core_Model_Abstract
{
	const NEW_ORDER = 'new_order';
	const ERROR = 'error';
	const NOTIFICATION = 'notification';
	
	protected $_types = array(
			self::NEW_ORDER => 'New Order',
			self::ERROR => 'Error',
	        self::NOTIFICATION => 'Notification',
	);
	
	/**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('extensions_store_storealerts/alert');
    }	

    public function getTypes()
    {
    	return $this->_types;
    }
        
}