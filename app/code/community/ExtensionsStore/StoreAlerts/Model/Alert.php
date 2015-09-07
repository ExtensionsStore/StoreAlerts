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
	const CONTACT = 'contact';
	const NOTIFICATION = 'notification';
	const LOG = 'log';
	
	protected $_types = array(
			self::NEW_ORDER => array('label' => 'New Orders', 'title' => 'New Order'),
			self::CONTACT => array('label' => 'Contacts', 'title' => 'Contact'),
	        self::NOTIFICATION => array('label' => 'Admin Notifications', 'title' => 'Notification'),
			self::LOG => array('label' => 'Log Messages', 'title'=> 'Log Message'),
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