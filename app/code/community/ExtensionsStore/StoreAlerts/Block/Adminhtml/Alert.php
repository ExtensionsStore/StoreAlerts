<?php

/**
 * Store Alerts admin
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
 
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Alert extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_blockGroup = 'extensions_store_storealerts';
		$this->_controller = 'adminhtml_alert';
		$this->_headerText = Mage::helper('storealerts')->__('Store Alerts');

		parent::__construct();
		$this->_removeButton('add');
	}
}
