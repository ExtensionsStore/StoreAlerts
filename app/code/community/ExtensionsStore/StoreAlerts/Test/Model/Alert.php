<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Test_Model_Alert extends EcomDev_PHPUnit_Test_Case {
	public function setUp() {
		$collection = Mage::getModel ( 'storealerts/alert' )->getCollection ();
		foreach ( $collection as $error ) {
			$error->delete ();
		}
	}
	/**
	 * @test
	 * @loadFixture alert
	 */
	public function testAlert() {
		echo "\nStarting alert model test.";
		
		$message = uniqid ();
		
		// test if logging is working
		$priority = Mage::getStoreConfig ( 'extensions_store_storealerts/configuration/log_level' );
		Mage::log ( $message, $priority );
		$alert = Mage::getModel ( 'storealerts/alert' )->load ( $message, 'message' );
		$logged = ($alert->getId ()) ? true : false;
		$this->assertTrue ( $logged );
		
		// test if configured log level is working
		$message = uniqid ();
		$priority = $priority + 1;
		Mage::log ( $message, $priority );
		$alert = Mage::getModel ( 'storealerts/alert' )->load ( $message, 'message' );
		$logged = ($alert->getId ()) ? true : false;
		$this->assertFalse ( $logged );
		
		echo "\nCompleted alert model test.";
	}
	public function tearDown() {
		$collection = Mage::getModel ( 'storealerts/alert' )->getCollection ();
		foreach ( $collection as $error ) {
			$error->delete ();
		}
	}
}