<?php

/**
 * 
 * @category    StoreDebug
 * @package     StoreDebug_ErrorLog
 * @author      Store Debug <admin@storedebug.com>
 */

class StoreDebug_ErrorLog_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
    
    /**
     * @test
     */    
    public function test()
    {
        echo "\nStarting helper test.";
        
        $message = uniqid();
        $title = $message;
		$priority = Mage::getStoreConfig ( 'storedebug_errorlog/configuration/log_level' );
        $datetime = date('Y-m-d H:i:s');
        
        $helper = Mage::helper('errorlog');
        
        $helper->saveError($message, $title, $priority, $datetime);
        
        $error = Mage::getModel ( 'errorlog/error' )->load ( $message, 'message' );
        $errorSaved = ($error->getId ()) ? true : false;
        $this->assertTrue ( $errorSaved );
        $priorityMatched = ($error->getPriority () == $priority) ? true : false;
        $this->assertTrue ( $priorityMatched );
        $datetimeMatched = ($error->getCreatedAt () == $datetime) ? true : false;
        $this->assertTrue ( $datetimeMatched );
        
        echo "\nCompleted helper test.";
    }
    
}