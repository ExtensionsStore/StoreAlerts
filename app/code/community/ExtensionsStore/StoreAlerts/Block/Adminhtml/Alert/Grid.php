<?php

/**
 * Store Alerts admin
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
 
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Alert_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('storealerts_grid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('storealerts/alert')->getCollection();
		$user = Mage::getSingleton('admin/session')->getUser();
		$userId = $user->getId();
		$collection->addFieldToFilter('user_id', $userId);
		$collection->setOrder('created_at','DESC');
		
		$this->setCollection($collection);
		parent::_prepareCollection();
		return $this;
	}

	protected function _prepareColumns()
	{
		$helper = Mage::helper('storealerts');

		$this->addColumn('id', array(
				'header' => $helper->__('ID'),
				'index'  => 'id'
		));

		$this->addColumn('label', array(
				'header' => $helper->__('Alert Type'),
				'index'  => 'label'
		));

		$this->addColumn('title', array(
				'header' => $helper->__('Title'),
				'index'  => 'title'
		));

		$this->addColumn('message', array(
				'header' => $helper->__('Message'),
				'index'  => 'message'
		));
		$this->addColumn('created_at', array(
				'header' => $helper->__('Datetime'),
				'type' => 'datetime',
				'index'  => 'created_at'
		));
		$this->addColumn ( 'action', array (
				'header' => $helper->__ ( 'Action' ),
				'width' => '100',
				'type' => 'action',
				'getter' => 'getId',
				'actions' => array (
						array (
								'caption' => $helper->__ ( 'View' ),
								'url' => array (
										'base' => '*/*/view' 
								),
								'field' => 'id' 
						),					
				),
				'filter' => false,
				'is_system' => true,
				'sortable' => false 
		) );
		
		$this->addExportType('*/*/exportCsv', $helper->__('CSV'));
		$this->addExportType('*/*/exportExcel', $helper->__('Excel XML'));

		return parent::_prepareColumns();
	}
	
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('alert_ids');
		$this->getMassactionBlock()->setFormFieldName('alert_id');
		 
		$this->getMassactionBlock()->addItem('delete', array(
			'label'=> Mage::helper('storealerts')->__('Delete'),
			'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
			'confirm' => Mage::helper('storealerts')->__('Are you sure?')
		));
		 
		return $this;
				
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/view', array(
				'id'=>$row->getId())
		);
	}
	
}
