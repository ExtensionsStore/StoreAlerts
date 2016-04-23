<?php

/**
 * Store Alerts devices
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Adminhtml_DeviceController extends Mage_Adminhtml_Controller_Action {
	public function indexAction() {
		$this->_title ( $this->__ ( 'System' ) )->_title ( $this->__ ( 'Store Alerts' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'system/storealerts' );
		$this->renderLayout ();
	}
	public function gridAction() {
		$this->loadLayout ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'storealerts/adminhtml_device_grid' )->toHtml () );
	}
	public function editAction() {
		$this->_title ( $this->__ ( 'System' ) )->_title ( $this->__ ( 'Store Alerts' ) );
		
		$user = Mage::getSingleton ( 'admin/session' )->getUser ();
		$userId = $user->getId ();
		
		$deviceId = ( int ) $this->getRequest ()->getParam ( 'id' );
		$device = Mage::getModel ( 'storealerts/device' );
		$device->load ( $deviceId );
		
		if ($device->getId () && $device->getUserId () == $userId) {
			
			Mage::register ( 'device', $device );
			$this->loadLayout ();
			$this->_setActiveMenu ( 'system/storealerts' );
			$this->renderLayout ();
		} else {
			$this->_redirect ( '*/*/' );
		}
	}
	public function deleteAction() {
		if ($deviceId = $this->getRequest ()->getParam ( 'id' )) {
			try {
				$user = Mage::getSingleton ( 'admin/session' )->getUser ();
				$userId = $user->getId ();
				$device = Mage::getModel ( 'storealerts/device' );
				$device->load ( $deviceId );
				
				if ($device->getId () && $device->getUserId () == $userId) {
					
					$device->delete ();
					Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'storealerts' )->__ ( 'The device has been deleted.' ) );
					$this->_redirect ( '*/*/' );
				} else {
					$this->_redirect ( '*/*/' );
				}
				
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				$this->_redirect ( '*/*/view', array (
						'id' => $deviceId 
				) );
				return;
			}
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'storealerts' )->__ ( 'Unable to find an device to delete.' ) );
		$this->_redirect ( '*/*/' );
	}
	
	/**
	 * Save action
	 */
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			
			try {
				$user = Mage::getSingleton ( 'admin/session' )->getUser ();
				$userId = $user->getId ();
				
				$deviceId = ( int ) $data ['id'];
				
				if ($deviceId) {
					$device = Mage::getModel ( 'storealerts/device' );
					$device->load ( $deviceId );
					
					if ($device->getId () && $device->getUserId () == $userId) {
						
						$data ['updated_at'] = date ( 'Y-m-d H:i:s' );
						
						$device->addData ( $data );
						$device->save ();
						$this->_getSession ()->addSuccess ( Mage::helper ( 'storealerts' )->__ ( 'The device has been saved.' ) );
						
						Mage::getSingleton ( 'adminhtml/session' )->setFormData ( false );
						// check if 'Save and Continue'
						if ($this->getRequest ()->getParam ( 'back' )) {
							$this->_redirect ( '*/*/edit', array (
									'device_id' => $device->getId (),
									'_current' => true 
							) );
							return;
						}
					}
				}
				
				// go to grid
				$this->_redirect ( '*/*/' );
				return;
			} catch ( Mage_Core_Exception $e ) {
				
				$this->_getSession ()->addError ( $e->getMessage () );
			} catch ( Exception $e ) {
				
				$this->_getSession ()->addException ( $e, Mage::helper ( 'storealerts' )->__ ( 'An error occurred while saving the device.' ) );
			}
			
			$this->_getSession ()->setFormData ( $data );
			$this->_redirect ( '*/*/edit', array (
					'id' => $this->getRequest ()->getParam ( 'id' ) 
			) );
			return;
		}
		
		$this->_redirect ( '*/*/' );
	}
	public function massDeleteAction() {
		$deviceIds = $this->getRequest ()->getParam ( 'device_id' );
		if (! is_array ( $deviceIds )) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'storealerts' )->__ ( 'Please select device(s) to delete.' ) );
		} else {
			try {
				$deleted = 0;
				$user = Mage::getSingleton ( 'admin/session' )->getUser ();
				$userId = $user->getId ();
				
				$deviceModel = Mage::getModel ( 'storealerts/device' );
				foreach ( $deviceIds as $deviceId ) {
					$deviceModel->load ( $deviceId );
					if ($deviceModel->getId () && $deviceModel->getUserId () == $userId) {
						$deviceModel->delete ();
						$deleted ++;
					}
				}
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'storealerts' )->__ ( 'Total of %d device(s) were deleted.', $deleted ) );
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
			}
		}
		
		$this->_redirect ( '*/*/index' );
	}
	public function exportCsvAction() {
		$fileName = 'devices.csv';
		$grid = $this->getLayout ()->createBlock ( 'storealerts/adminhtml_device_grid' );
		$this->_prepareDownloadResponse ( $fileName, $grid->getCsvFile () );
	}
	public function exportExcelAction() {
		$fileName = 'devices.xml';
		$grid = $this->getLayout ()->createBlock ( 'storealerts/adminhtml_device_grid' );
		$this->_prepareDownloadResponse ( $fileName, $grid->getExcelFile ( $fileName ) );
	}
	protected function _isAllowed() {
		return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'system/storealerts' );
	}
}
