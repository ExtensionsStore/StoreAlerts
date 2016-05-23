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
    	
    	$billingAddress = $order->getBillingAddress();
    	$shippingAddress = $order->getBillingAddress();
    	$billingAddressLabel = $helper->__('Billing Address');
    	$billingAddressStr = $billingAddress->getFormated(false);
    	$billingAddressStr = preg_replace('/\s+/', ' ',$billingAddressStr);
    	$shippingAddressLabel = $helper->__('Shipping Address');
    	$shippingAddressStr = $shippingAddress->getFormated(false);
    	$shippingAddressStr = preg_replace('/\s+/', ' ',$shippingAddressStr);
    	$itemsLabel = $helper->__('Items');
    	$items = $order->getAllVisibleItems();
    	$itemsStr = '';
    	foreach ($items as $item){
    	    $itemsStr .= $item->getSku(). ' ' .$item->getName()."\n";
    	}
    	$itemsStr = trim($itemsStr);
    	$shippingMethodLabel = $helper->__('Shipping Method');
    	$shippingMethodStr = $order->getShippingDescription();
    	$paymentMethodLabel = $helper->__('Payment Method');
    	$paymentMethodStr = $order->getPayment()->getMethod();
    	$subtotalLabel = $helper->__('Subtotal');
    	$subtotalStr = Mage::helper('core')->currency($order->getSubtotal(), true, false);
    	$shippingLabel = $helper->__('Shipping');
    	$shippingStr = Mage::helper('core')->currency($order->getShippingAmount(), true, false);
    	$taxLabel = $helper->__('Tax');
    	$taxStr = Mage::helper('core')->currency($order->getTaxAmount(), true, false);
    	$grandTotalLabel = $helper->__('Grand Total');
    	$grandTotalStr = Mage::helper('core')->currency($order->getGrandTotal(), true, false);
    	 
    	$message = "$billingAddressLabel: $billingAddressStr
$shippingAddressLabel: $shippingAddressStr
$itemsLabel: $itemsStr
$shippingMethodLabel: $shippingMethodStr
$paymentMethodLabel: $paymentMethodStr
$subtotalLabel: $subtotalStr
$shippingLabel: $shippingStr
$taxLabel: $taxStr
$grandTotalLabel: $grandTotalStr";   	
    	
    	$message = trim($message);
    	$title = $helper->__('New order').': '.$grandTotalStr;
    	 
    	$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::NEW_ORDER, $message, $title);
        
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
    	
    	$title = $helper->__('New message').': '. $post['name'];
    	 
    	$message = 
    		$helper->__('Name').': '.$post['name']. "\n".
    		$helper->__('Email').': '.$post['email'] . "\n".
    		$helper->__('Telephone').': '.$post['telephone'] ."\n";
    	$message .= $helper->__('Comment').': '.$post['comment'];
    	$message = trim($message);
    	 
    	$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::CONTACT, $message, $title);
    
    	return $observer;
    }    
    
}