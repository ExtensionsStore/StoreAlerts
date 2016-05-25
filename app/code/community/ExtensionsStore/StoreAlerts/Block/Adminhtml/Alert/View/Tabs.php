<?php

/**
 * View alert form tabs
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Alert_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    /**
     * Initialize Tabs
     */
    public function __construct() {
        parent::__construct();
        $this->setId('alert_tabs');
        $this->setDestElementId('view_form');
        $this->setTitle(Mage::helper('storealerts')->__('Alert Info'));
    }

    /**
     * Retrieve visitor entity
     * 
     * @return ExtensionsStore_StoreAlerts_Model_Alert
     */
    public function getAlert() {
        return Mage::registry('alert');
    }

}