<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Device extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('extensions_store_storealerts/device');
    }	
	
    /**
     * Get alerts for this device
     * 
     * @param int $page
     * @param int $limit
     * 
     */
    public function getAlertsArray($page = 1, $limit = 20)
    {
    	$result = array();
    	
    	if ($this->getId()){
    		
    		$userId = $this->getUserId();
    		
    		$collection = Mage::getModel('extensions_store_storealerts/alert')->getCollection();
    		$collection->addFieldToFilter('user_id', $userId);
    		$collection->setOrder('created_at','DESC');
    		$collection->setPageSize($limit);
    		$collection->setCurPage($page);
    		 
    		$data = array(
    				'totalRecords' => $collection->getSize()
    		);
    		 
    		$dates = array();
    		 
    		foreach ($collection as $alert){
    		
    			$createdAt = $alert->getCreatedAt();
    			$createdAtTime = strtotime($createdAt);
    			$date = date('Y-m-d', $createdAtTime);
    		
    			$dates[$date][] = $alert->getData();
    		
    		}
    		 
    		$data['dates'] = $dates;
    		
    		$result['error'] = false;
    		$result['data'] = $data;   
    		
    	} else {
    		
    		$result['error'] = true;
    		$result['data'] = 'Could not load device.';
    	}
    	
    	return $result;
    }
    
    /**
     * Delete alert owned by this device
     * 
     * @param int $alertId
     * @return array
     */
    public function deleteAlert($alertId)
    {
    	$result = array();
    	
    	if ($this->getId()){
    		try {
    		
    			$alertModel = Mage::getModel('extensions_store_storealerts/alert');
    			$alertModel->load($alertId);
    		
    			if ($alertModel->getId()){
    				 
    				if ($alertModel->getUserId() == $this->getUserId()){
    		
    					$alertModel->delete();
    					$result = $this->getAlertsArray();
    		
    				} else {
    		
    					$result['error'] = true;
    					$result['data'] = 'Alert does not belong to device.';
    				}
    		
    				 
    			} else {
    				 
    				$result['error'] = true;
    				$result['data'] = 'Could not load alert.';
    		
    			}
    		
    		} catch(Exception $e){
    		
    			$result['error'] = true;
    			$result['data'] = $e->getMessage();
    		}
    		     		
    	} else {
    		$result['error'] = true;
    		$result['data'] = 'Could not load device.';
    	}
    	
    	
    	return $result;
    }
}