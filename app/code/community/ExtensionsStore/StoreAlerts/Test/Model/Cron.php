<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Test_Model_Cron extends EcomDev_PHPUnit_Test_Case
{
	protected $_reportIds = array();
	public function setUp() {
		//create ten reports
	    $reportDir = Mage::getBaseDir('var').DS.'report';
		if (!is_dir($reportDir)){
		    mkdir($reportDir, 0777);
		}
		for ($i=0; $i<10;$i++){
			$reportId   = abs(intval(microtime(true) * rand(100, 1000)));
			$this->_reportIds[] = $reportId;
			
			$reportFile = $reportDir.DS.$reportId;
			$reportData[0] = $reportId;
			$reportData[1] = uniqid();
			file_put_contents($reportFile, serialize($reportData));
			chmod($reportFile,0666);
		}
		$priority = Mage::getStoreConfig ( 'extensions_store_storealerts/configuration/log_level' );
		$systemArchiveFile = 'system_archive.log';
		$exceptionArchiveFile = 'exception_archive.log';
		//create ten logs
		for ($i=0; $i<10;$i++){
			$message = uniqid();
			Mage::log($message,$priority, $systemArchiveFile);
		}
		//create ten exception logs
		for ($i=0; $i<10;$i++){
			$message = uniqid();
			Mage::log($message,$priority, $exceptionArchiveFile);
		}
		//delete previous
		$collection = Mage::getModel ( 'storealerts/alert' )->getCollection ();
		foreach ( $collection as $error ) {
			$error->delete ();
		}		
	}	
    /**
     * @test
     */    
    public function testArchive()
    {
        echo "\nStarting cron test.";
        
		$cron = Mage::getModel('storealerts/cron');
		$schedule = Mage::getModel('cron/schedule');
		$result = $cron->archive($schedule);
		$matches = array();
		preg_match('/Number of exception reports archived: (\d+); number of system log archived: (\d+); number of exception log archived: (\d+)/', $result, $matches);
		$this->assertGreaterThan(0, $matches[1]);
		$this->assertGreaterThan(0, $matches[2]);
		$this->assertGreaterThan(0, $matches[3]);
		
		echo "\nCompleted cron test.";
    }
    public function tearDown() {
    	foreach ($this->_reportIds as $reportId){
    		$report = Mage::getBaseDir('var').DS.'report'.DS.$reportId;
    		$reportLogged = Mage::getBaseDir('var').DS.'report'.DS.'logged'.DS.$reportId;
    		if (file_exists($reportLogged)){
    			unlink($reportLogged);
    		} else if (file_exists($report)) {
    			unlink($report);
    		} else {
    			echo 'Test report file does not exist '.$report;
    		}
    	}
    	$collection = Mage::getModel ( 'storealerts/alert' )->getCollection ();
    	foreach ( $collection as $error ) {
    		$error->delete ();
    	}
    }    
    
}