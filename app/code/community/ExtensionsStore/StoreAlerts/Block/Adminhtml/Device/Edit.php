<?php

/**
 * Edit device form
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Device_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	public function __construct() {
		parent::__construct ();
		$this->_objectId = 'id';
		$this->_blockGroup = 'storealerts';
		$this->_controller = 'adminhtml_device';
		$this->_mode = 'edit';
		$device = Mage::registry ( 'device' );
		$this->_headerText = $this->helper ( 'storealerts' )->__ ( 'Device: %s', $device->getAccessToken () );
		// $this->_removeButton('reset');
		
		if ($this->_isAllowedAction ( 'save' )) {
			$this->_updateButton ( 'save', 'label', $this->helper ( 'storealerts' )->__ ( 'Save Device' ) );
			$this->_addButton ( 'saveandcontinue', array (
					'label' => Mage::helper ( 'adminhtml' )->__ ( 'Save and Continue Edit' ),
					'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl () . '\')',
					'class' => 'save' 
			), - 100 );
		} else {
			$this->_removeButton ( 'save' );
		}
	}
	public function getSaveUrl() {
		return $this->getUrl ( '*/*/save' );
	}
	protected function _getSaveAndContinueUrl() {
		return $this->getUrl ( '*/*/save', array (
				'_current' => true,
				'back' => 'edit',
				'active_tab' => '{{tab_id}}' 
		) );
	}
	
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
	
	/**
	 * Prepare layout
	 *
	 * @return Mage_Core_Block_Abstract
	 */
	protected function _prepareLayout() {
		$tabsBlock = $this->getLayout ()->getBlock ( 'device_edit_tabs' );
		if ($tabsBlock) {
			$tabsBlockJsObject = $tabsBlock->getJsObjectName ();
			$tabsBlockPrefix = $tabsBlock->getId () . '_';
		} else {
			$tabsBlockJsObject = 'device_tabsJsTabs';
			$tabsBlockPrefix = 'device_tabs_';
		}
		$this->_formScripts [] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            }
            function saveAndContinueEdit(urlTemplate) {
                var tabsIdValue = " . $tabsBlockJsObject . ".activeTab.id;
                var tabsBlockPrefix = '" . $tabsBlockPrefix . "';
                if (tabsIdValue.startsWith(tabsBlockPrefix)) {
                    tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
                }
                var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
                var url = template.evaluate({tab_id:tabsIdValue});
                editForm.submit(url);
            }
        ";
		return parent::_prepareLayout ();
	}
}