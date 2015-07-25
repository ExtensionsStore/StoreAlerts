<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Observer
{
    /**
     * Observer for new order
     * 
     * @param Varien_Event_Observer $observer
     */
    public function newOrderAlert($observer)
    {

    	$helper = Mage::helper('storealerts');
    	$order = $observer->getOrder();
    	
    	$grandTotal = Mage::helper('core')->currency($order->getGrandTotal(), true, false);
    	$message = $helper->__('New order: '). $grandTotal;
    	
    	$this->_saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::NEW_ORDER, $message);
        
        return $observer;
        
    }    
    
    /**
     * Register alert for each subscriber
     * 
     * @param string $type
     * @param string $message
     */
    protected function _saveAlert($type, $message)
    {
    	try {
    		
    		$devices = Mage::getModel('storealerts/device')->getCollection();
    		
    		foreach ($devices as $device){
    			
    			$alertsStr = trim($device->getAlerts());
    			$selectedAlerts = explode(',', $alertsStr);
    			
    			if (is_array($selectedAlerts) && in_array($type, $selectedAlerts)){
    				
    				$alertIndex = array_search($type, $selectedAlerts);
    				
    				$soundsStr = trim($device->getSounds());
    				$alertSounds = explode(',', $soundsStr);
    				$sound = (is_array($alertSounds) && count($alertSounds) == count($selectedAlerts)) ? 
    					$alertSounds[$alertIndex] : 'default';
    				$userId = $device->getUserId();
    				
    				$datetime = date('Y-m-d H:i:s');
    				 
    				$alert = Mage::getModel('storealerts/alert');
    				$alert->setMessage($message);
    				$alert->setSound($sound);
    				$alert->setUserId($userId);
    				$alert->setSent(0);
    				$alert->setCreatedAt($datetime);
    				$alert->setUpdatedAt($datetime);
    				$alert->save();
    				
    			}
    			 
    		}
    		
    		
    	}catch(Exception $e){
    		
            Mage::helper('storealerts')->log($e->getMessage());
    	}
    	 
    }
    
    
}