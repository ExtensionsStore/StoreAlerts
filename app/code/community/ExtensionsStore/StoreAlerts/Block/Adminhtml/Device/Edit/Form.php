<?php

/**
 * Edit device form
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Device_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
		$form = new Varien_Data_Form ( array (
				'id' => 'edit_form',
				'action' => $this->getData ( 'action' ),
				'method' => 'post',
				'enctype' => 'multipart/form-data' 
		) );
		
		$fieldset = $form->addFieldset ( 'base_fieldset', array (
				'legend' => $this->helper ( 'storealerts' )->__ ( 'Device Detail' ) 
		) );
		
		$fieldset->addField ( 'id', 'hidden', array (
				'name' => 'id' 
		) );
		
		$fieldset->addField ( 'name', 'text', array (
				'label' => $this->helper ( 'storealerts' )->__ ( 'Device Name' ),
				'name' => 'name',
				'required' => false
		) );
		
		
		$fieldset->addField ( 'device_token', 'text', array (
				'label' => $this->helper ( 'storealerts' )->__ ( 'Device Token' ),
				'name' => 'device_token',
				'required' => true 
		) );
		
		$fieldset->addField ( 'access_token', 'text', array (
				'label' => $this->helper ( 'storealerts' )->__ ( 'Access Token' ),
				'name' => 'access_token',
				'required' => false 
		) );
		
		$device = Mage::registry ( 'device' );
		
		$form->setValues ( $device->getData () );
		
		$form->setUseContainer ( true );
		$this->setForm ( $form );
		return parent::_prepareForm ();
	}
}