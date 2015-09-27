<?php

/**
 * Store Alerts admin
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Adminhtml_AlertController extends Mage_Adminhtml_Controller_Action {
	public function indexAction() {
		$this->_title ( $this->__ ( 'System' ) )->_title ( $this->__ ( 'Store Alerts' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'system/storealerts' );
		$this->renderLayout ();
	}
	public function gridAction() {
		$this->loadLayout ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'storealerts/adminhtml_alert_grid' )->toHtml () );
	}
	public function viewAction() {
		$this->_title ( $this->__ ( 'System' ) )->_title ( $this->__ ( 'Store Alerts' ) );
		
		$user = Mage::getSingleton('admin/session')->getUser();
		$userId = $user->getId();
		
		$alertId = ( int ) $this->getRequest ()->getParam ( 'id' );
		$alert = Mage::getModel ( 'storealerts/alert' );
		$alert->load ( $alertId );
		
		if ($alert->getId() && $alert->getUserId()==$userId){
			
			Mage::register ( 'alert', $alert );
			$this->loadLayout ();
			$this->_setActiveMenu ( 'system/storealerts' );
			$this->renderLayout ();			
			
		} else {
			$this->_redirect ( '*/*/' );
		}

	}
	public function deleteAction() {
		if ($alertId = $this->getRequest ()->getParam ( 'id' )) {
			try {
				$user = Mage::getSingleton('admin/session')->getUser();
				$userId = $user->getId();
				$alert = Mage::getModel ( 'storealerts/alert' );
				$alert->load ( $alertId );
				
				if ($alert->getId() && $alert->getUserId()==$userId){
						
					$alert->delete();
					Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'storealerts' )->__ ( 'The alert has been deleted.' ) );
					$this->_redirect ( '*/*/' );
						
				} else {
					$this->_redirect ( '*/*/' );
				}				

				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				$this->_redirect ( '*/*/view', array (
						'id' => $alertId 
				) );
				return;
			}
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'storealerts' )->__ ( 'Unable to find an alert to delete.' ) );
		$this->_redirect ( '*/*/' );
	}
	
	public function massDeleteAction()
	{
		$alertIds = $this->getRequest()->getParam('alert_id');
		if(!is_array($alertIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storealerts')->__('Please select alert(s) to delete.'));
		} else {
			try {
				$deleted = 0;
				$user = Mage::getSingleton('admin/session')->getUser();
				$userId = $user->getId();
				
				$alertModel = Mage::getModel('storealerts/alert');
				foreach ($alertIds as $alertId) {
					$alertModel->load($alertId);
					if ($alertModel->getId() && $alertModel->getUserId()==$userId){
						$alertModel->delete();
						$deleted++;
					}
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('storealerts')->__(
								'Total of %d alert(s) were deleted.', $deleted
						)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		
		$this->_redirect('*/*/index');
		
	}
	public function exportCsvAction() {
		$fileName = 'alerts.csv';
		$grid = $this->getLayout ()->createBlock ( 'storealerts/adminhtml_alert_grid' );
		$this->_prepareDownloadResponse ( $fileName, $grid->getCsvFile () );
	}
	public function exportExcelAction() {
		$fileName = 'alerts.xml';
		$grid = $this->getLayout ()->createBlock ( 'storealerts/adminhtml_alert_grid' );
		$this->_prepareDownloadResponse ( $fileName, $grid->getExcelFile ( $fileName ) );
	}
	
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')
		->isAllowed('system/storealerts');
	}
	
}
