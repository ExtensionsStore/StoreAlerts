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
    
    public function getPreferences()
    {
    	$result = array();
    	 
    	$alertModel = Mage::getModel('extensions_store_storealerts/alert');
   		$types = $alertModel->getTypes();
  		$alerts = explode(',',$this->getAlerts());
    			
 		$data = array();
    			
    	foreach($types as $type=>$typeAr){
    		$data[] = array(
    			'type' => $type,
    			'label' => $typeAr['label'],
    			'selected' => in_array($type, $alerts)
    		);
    	}
    	
    	$result['error'] = false;
    	$result['data'] = $data;
    	 
    	return $result;    	
    }
    
    public function updatePreferences($dataObj)
    {
    	$result = array();
    	
    	$alertModel = Mage::getModel('extensions_store_storealerts/alert');
    	$types = $alertModel->getTypes();
    	$alertsAr = array();
    	     	 
 		$data = array();
 		
    	foreach($types as $type=>$label){
			if ($dataObj->getData($type)){
				$alertsAr[] = $type;
			}
			$data[] = array(
					'type' => $type,
					'label' => $label,
					'selected' => in_array($type, $alertsAr)
			);			
    	}
    	
    	$alerts = implode(',', $alertsAr);
    	$this->setAlerts($alerts);
    	
    	try {
    		
    		$datetime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
    		
    		if (!$this->getId()){
    			$this->setCreatedAt($datetime);
    		}
    		$this->setUpdatedAt($datetime);
    		
    		$this->save();
    		    		
	    	$result['error'] = false;
	    	$result['data'] = $data;
	    	
    	} catch(Exception $e){
    		
    		$result['error'] = true;
    		$result['data'] = $e->getMessage();;
    	}
    	
    	return $result;    	
    }
	
    /**
     * Get alerts for this device
     * 
     * @param string $username
     * @param string $password
     * @param string $fromDate
     */
    public function getAlertsArray($fromDate = null)
    {
    	$result = array();
    	
    	$userId = $this->getUserId();
    		
   		$collection = Mage::getModel('extensions_store_storealerts/alert')->getCollection();
   		$collection->addFieldToFilter('user_id', $userId);
   		$fromDateTS = strtotime($fromDate);
   		if ($fromDateTS){
    		$fromDateFormatted = date('Y-m-d',$fromDateTS);
    		$collection->addFieldToFilter('date_created', array('gt' => $fromDateFormatted));
    	}
    	$collection->setOrder('created_at','DESC');
    	
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
    	
    	return $result;
    }
}