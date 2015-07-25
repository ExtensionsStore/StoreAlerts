<?php

/**
 * Set current admin alerts
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
 
class ExtensionsStore_StoreAlerts_Block_Adminhtml_System_Config_Form_Field_Alerts 
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	const PATH = 'extensions_store_storealerts/configuration/alerts';

	/**
	 * 
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_System_Config_Form_Field::_getElementHtml()
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$adminUser = Mage::getSingleton('admin/session')->getUser();
		$device = Mage::getModel('extensions_store_storealerts/device');
		$device->load($adminUser->getId(), 'user_id');
		
		$alerts = ($device->getId()) ? $device->getAlerts() : '';
		
		$element->setValue($alerts);
		
		return parent::_getElementHtml($element);
	}
}