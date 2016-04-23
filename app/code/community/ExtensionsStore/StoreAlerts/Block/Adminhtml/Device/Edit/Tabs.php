<?php

/**
 * Edit device form tabs
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Device_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	
	/**
	 * Initialize Tabs
	 */
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'device_tabs' );
		$this->setDestElementId ( 'edit_form' );
		$this->setTitle ( Mage::helper ( 'storealerts' )->__ ( 'Device Info' ) );
	}
	
	/**
	 * Retrieve device entity
	 *
	 * @return storealerts/device
	 */
	public function getDevice() {
		return Mage::registry ( 'device' );
	}
}