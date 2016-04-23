<?php

/**
 * View alert info tab
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Device_Edit_Tab_Detail extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	/**
	 * Check permission for passed action
	 *
	 * @param string $action        	
	 * @return bool
	 */
	protected function _isAllowedAction($action) {
		$allowed = Mage::getSingleton ( 'admin/session' )->isAllowed ( 'system/storealerts/' . $action );
		return $allowed;
	}
	public function getDevice() {
		return Mage::registry ( 'device' );
	}
	public function getTabLabel() {
		return $this->helper ( 'storealerts' )->__ ( 'Info' );
	}
	public function getTabTitle() {
		return $this->helper ( 'storealerts' )->__ ( 'Info' );
	}
	public function canShowTab() {
		if (Mage::registry ( 'device' )->getId ()) {
			return true;
		}
		
		return false;
	}
	public function isHidden() {
		if (Mage::registry ( 'device' )->getId ()) {
			
			return false;
		}
		
		return true;
	}
}