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
    		$message = trim($event['message']);
    		$title = trim(substr($message,0,80));
    		$title .= (strlen($message)>80) ? '...' : '';
    		
    		$logLevel = (int)Mage::getStoreConfig('extensions_store_storealerts/configuration/log_level');
    		
    		if (is_numeric($priority) && $priority <= $logLevel){
    			$helper = Mage::helper('storealerts');
    			$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $message, $title);
    		}
    	}
        
        parent::_write($event);

    }
	
}