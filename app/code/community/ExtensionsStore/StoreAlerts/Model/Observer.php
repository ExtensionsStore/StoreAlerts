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
     * Observer for contact
     *
     * @param Varien_Event_Observer $observer
     */
    public function contactAlert($observer)
    {
    	$helper = Mage::helper('storealerts');
    	$controllerAction = $observer->getControllerAction();
    	$post = $controllerAction->getRequest()->getPost();
    	 
    	$message = $helper->__('Message from: '). 
    		$post['name']. ' ' .
    		$post['email'] . ' ' .
    		$post['telephone'];
    	$message = trim($message);
    	$message .= '. Message: ' .$post['comment'];
    	 
    	$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::CONTACT, $message);
    
    	return $observer;
    }    
    
}