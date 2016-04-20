<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Preference extends Mage_Core_Model_Abstract
{
	/**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('extensions_store_storealerts/preference');
    }	

    /**
     * Get preferences for this device
     *
     * @return array
     */
    public function getPreferences()
    {
    	$result = array();
    
    	if ($this->getId()){
    
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
    
    	}else {
    
    		$result['error'] = true;
    		$result['data'] = 'Could not load user preference.';
    	}
    
    	return $result;
    }
    
    /**
     *
     * @param Varien_Object $dataObj
     * @return array
     */
    public function updatePreferences($dataObj)
    {
    	$result = array();
    	 
    	if ($this->getId()){
    
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
    
    			$datetime = date("Y-m-d H:i:s");
    
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
    
    	} else {
    
    		$result['error'] = true;
    		$result['data'] = 'Could not load user preferences.';
    	}
    	 
    	return $result;
    }    
}