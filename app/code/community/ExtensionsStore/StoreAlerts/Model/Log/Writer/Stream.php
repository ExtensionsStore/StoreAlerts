<?php

/**
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
 
class ExtensionsStore_StoreAlerts_Model_Log_Writer_Stream extends Zend_Log_Writer_Stream
{
	protected $_log;
	
	/**
	 * Store alerts for logs into system.log and exception.log
	 *
	 * @param array|string|resource $streamOrUrl Stream or URL to open as a stream
	 * @param string|null $mode Mode, only applicable if a URL is given
	 * @return void
	 * @throws Zend_Log_Exception
	 */
	public function __construct($streamOrUrl, $mode = null)
	{
		$file = Mage::getStoreConfig('dev/log/file');
		$exceptionFile = Mage::getStoreConfig('dev/log/exception_file');
		
		if (is_numeric(strpos($streamOrUrl, $file)) || is_numeric(strpos($streamOrUrl, $exceptionFile))){
			$this->_log = true;
		}
		
		parent::__construct($streamOrUrl, $mode);
	}
	
    /**
     * Store alerts for log messages
     *
     * @param  array  $event  event data
     * @return void
     * @throws Zend_Log_Exception
     */
    protected function _write($event)
    {
    	if ($this->_log){
    		
    		$priority = (int)$event['priority'];
    		$message = $event['message'];
    		
    		$logLevel = (int)Mage::getStoreConfig('extensions_store_storealerts/configuration/log_level');
    		
    		if ($priority && $priority <= $logLevel){
    			
    			Mage::helper('storealerts')->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $message);
    		}
    	}
        
        parent::_write($event);

    }
	
}