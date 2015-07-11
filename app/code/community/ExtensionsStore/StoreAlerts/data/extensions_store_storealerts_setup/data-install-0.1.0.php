<?php

/**
 * 
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

$installer = $this;
$installer->startSetup();

$consumer = Mage::getModel('oauth/consumer');
$consumer->load('Store Alerts', 'name');



if (!$consumer->getId()) {

    try {

        $helper = Mage::helper('oauth');

        $data = array(
            'name' => 'Store Alerts',
            'key' => $helper->generateConsumerKey(),
            'secret' => $helper->generateConsumerSecret(),
            'callback_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/',
            'rejected_callback_url' => '',
        );

        $consumer->addData($data);
        $consumer->save();
        
    } catch (Exception $e) {

        Mage::log($e->getMessage(), null, 'extensions_store_storealerts.log');
    }
}

$installer->endSetup();
