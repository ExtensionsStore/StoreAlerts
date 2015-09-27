<?php

/**
 * View alert form
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
 
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Alert_View extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'storealerts';
        $this->_controller = 'adminhtml_alert';
        $this->_mode = 'view';
        $alert = Mage::registry('alert');
        $this->_headerText = $this->helper('storealerts')->__('Alert: %s', $alert->getTitle());
        $this->_removeButton('save');
        $this->_removeButton('reset');
    }
	
}