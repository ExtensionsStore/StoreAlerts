<?php

/**
 * Set current admin slack hooks
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
 
class ExtensionsStore_StoreAlerts_Block_Adminhtml_System_Config_Form_Field_Slackhooks 
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	const PATH = 'extensions_store_storealerts/configuration/slack_hooks';

	/**
	 * 
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_System_Config_Form_Field::_getElementHtml()
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$adminUser = Mage::getSingleton('admin/session')->getUser();
		$preference = Mage::getModel ( 'extensions_store_storealerts/preference' );
		$preference->load($adminUser->getId());
						
		$slackHooks = ($preference->getId()) ? $preference->getSlackHooks() : '';
		
		$element->setValue($slackHooks);
		
		return parent::_getElementHtml($element);
	}
}