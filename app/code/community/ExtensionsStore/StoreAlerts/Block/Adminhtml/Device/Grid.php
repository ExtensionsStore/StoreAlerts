<?php

/**
 * Store Alerts admin
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Device_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'device_grid' );
		$this->setDefaultSort ( 'id' );
		$this->setDefaultDir ( 'DESC' );
		$this->setSaveParametersInSession ( true );
		$this->setUseAjax ( true );
	}
	protected function _prepareCollection() {
		$collection = Mage::getModel ( 'storealerts/device' )->getCollection ();
		$user = Mage::getSingleton ( 'admin/session' )->getUser ();
		$userId = $user->getId ();
		$collection->addFieldToFilter ( 'user_id', $userId );
		$collection->setOrder ( 'created_at', 'DESC' );
		
		$this->setCollection ( $collection );
		parent::_prepareCollection ();
		return $this;
	}
	protected function _prepareColumns() {
		$helper = Mage::helper ( 'storealerts' );
		
		$this->addColumn ( 'id', array (
				'header' => $helper->__ ( 'ID' ),
				'index' => 'id' 
		) );
		
		$this->addColumn ( 'device_token', array (
				'header' => $helper->__ ( 'Device Token' ),
				'index' => 'device_token' 
		) );
		
		$this->addColumn ( 'access_token', array (
				'header' => $helper->__ ( 'Access Token' ),
				'index' => 'access_token' 
		) );
		
		$this->addColumn ( 'updated_at', array (
				'header' => $helper->__ ( 'Updated At' ),
				'type' => 'datetime',
				'index' => 'updated_at' 
		) );
		
		$this->addColumn ( 'action', array (
				'header' => $helper->__ ( 'Action' ),
				'width' => '100',
				'type' => 'action',
				'getter' => 'getId',
				'actions' => array (
						array (
								'caption' => $helper->__ ( 'Edit' ),
								'url' => array (
										'base' => '*/*/edit' 
								),
								'field' => 'id' 
						) 
				),
				'filter' => false,
				'is_system' => true,
				'sortable' => false 
		) );
		
		$this->addExportType ( '*/*/exportCsv', $helper->__ ( 'CSV' ) );
		$this->addExportType ( '*/*/exportExcel', $helper->__ ( 'Excel XML' ) );
		
		return parent::_prepareColumns ();
	}
	protected function _prepareMassaction() {
		$this->setMassactionIdField ( 'device_ids' );
		$this->getMassactionBlock ()->setFormFieldName ( 'device_id' );
		
		$this->getMassactionBlock ()->addItem ( 'delete', array (
				'label' => Mage::helper ( 'storealerts' )->__ ( 'Delete' ),
				'url' => $this->getUrl ( '*/*/massDelete', array (
						'' => '' 
				) ),
				'confirm' => Mage::helper ( 'storealerts' )->__ ( 'Are you sure?' ) 
		) );
		
		return $this;
	}
	public function getGridUrl() {
		return $this->getUrl ( '*/*/grid', array (
				'_current' => true 
		) );
	}
	public function getRowUrl($row) {
		return $this->getUrl ( '*/*/edit', array (
				'id' => $row->getId () 
		) );
	}
}
