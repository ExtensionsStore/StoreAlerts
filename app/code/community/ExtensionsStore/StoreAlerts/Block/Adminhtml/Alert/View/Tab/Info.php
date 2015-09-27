<?php

/**
 * View alert info tab
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Alert_View_Tab_Info 
	extends Mage_Adminhtml_Block_Template
	implements Mage_Adminhtml_Block_Widget_Tab_Interface 
{
	/**
	 * Check permission for passed action
	 *
	 * @param string $action
	 * @return bool
	 */
	protected function _isAllowedAction($action)
	{
		$allowed = Mage::getSingleton('admin/session')->isAllowed('system/storealerts/' . $action);
		return $allowed;
	}
	
	public function getAlert()
	{
		return Mage::registry('alert');
	}
	
	public function getTabLabel()
	{
		return $this->helper('storealerts')->__('Info');
	}
	
	public function getTabTitle()
	{
		return $this->helper('storealerts')->__('Info');
	}
	
	public function canShowTab()
	{
		if (Mage::registry('alert')->getId()) {
			return true;
		}
		
		return false;
	}
	
	public function isHidden()
	{
		if (Mage::registry('alert')->getId()) {
			
			return false;
		}
		
		return true;
	}
}