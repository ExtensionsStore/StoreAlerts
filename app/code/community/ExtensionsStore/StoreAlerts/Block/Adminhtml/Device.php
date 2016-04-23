<?php

/**
 * Devices
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Device extends Mage_Adminhtml_Block_Widget_Grid_Container {
	public function __construct() {
		$this->_blockGroup = 'extensions_store_storealerts';
		$this->_controller = 'adminhtml_device';
		$this->_headerText = Mage::helper ( 'storealerts' )->__ ( 'Devices' );
		
		parent::__construct ();
		$this->_removeButton ( 'add' );
	}
}
