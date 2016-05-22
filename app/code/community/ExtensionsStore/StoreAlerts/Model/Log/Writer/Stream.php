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
     * @param array|string|resource $streamOrUrl
     *            Stream or URL to open as a stream
     * @param string|null $mode
     *            Mode, only applicable if a URL is given
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __construct ($streamOrUrl, $mode = null)
    {
        $logFiles = explode(PHP_EOL, 
                Mage::getStoreConfig(
                        'extensions_store_storealerts/configuration/log_files'));
        $logFiles[] = Mage::getStoreConfig('dev/log/file');
        $logFiles[] = Mage::getStoreConfig('dev/log/exception_file');
        
        $logFile = basename($streamOrUrl);
        
        if (in_array($logFile, $logFiles)) {
            $this->_log = true;
        }
        
        parent::__construct($streamOrUrl, $mode);
    }

    /**
     * Store alerts for log messages
     *
     * @param array $event
     *            event data
     * @return void
     * @throws Zend_Log_Exception
     */
    protected function _write ($event)
    {
        if ($this->_log) {
            
            $helper = Mage::helper('storealerts');
            $priority = $event['priority'];
            
            if ($helper->logPriority($priority)) {
                
                $message = trim($event['message']);
                $exception = substr($message, 0, 9);
                if ($exception == 'exception') {
                    $matches = array();
                    preg_match_all("/'([^']+)'/", $message, $matches);
                    if (count($matches) == 2) {
                        $quotedStrings = $matches[1];
                        if (is_array($quotedStrings) && count($quotedStrings) > 1) {
                            $exceptionType = strtolower($quotedStrings[0]);
                            $exceptionMessage = $quotedStrings[1];
                            if (is_numeric(strpos($exceptionType, 'exception')) &&
                                     $exceptionMessage) {
                                $exceptionMsg = $exceptionMessage;
                            }
                        }
                    }
                }
                $exploded = (isset($exceptionMsg)) ? explode(":", $exceptionMsg) : explode(
                        ":", $message);
                $type = $exploded[0];
                
                if ($type) {
                    $type = trim($type);
                    switch ($type) {
                        case "Error":
                            $priority = Zend_Log::ERR;
                            break;
                        case "Warning":
                            $priority = Zend_Log::WARN;
                            break;
                        case "Parse Error":
                            $priority = Zend_Log::ERR;
                            break;
                        case "Notice":
                            $priority = Zend_Log::NOTICE;
                            break;
                        case "Core Error":
                            $priority = Zend_Log::ERR;
                            break;
                        case "Core Warning":
                            $priority = Zend_Log::WARN;
                            break;
                        case "Compile Error":
                            $priority = Zend_Log::ERR;
                            break;
                        case "Compile Warning":
                            $priority = Zend_Log::WARN;
                            break;
                        case "User Error":
                            $priority = Zend_Log::ERR;
                            break;
                        case "User Warning":
                            $priority = Zend_Log::WARN;
                            break;
                        case "User Notice":
                            $priority = Zend_Log::NOTICE;
                            break;
                        case "Strict Notice":
                            $priority = Zend_Log::DEBUG;
                            break;
                        case "Recoverable Error":
                            $priority = Zend_Log::ERR;
                            break;
                        case "Deprecated functionality":
                            $priority = Zend_Log::DEBUG;
                            break;
                        case "Payment authorization error":
                            $priority = Zend_Log::DEBUG;
                            break;
                        default:
                            break;
                    }
                }
                
                $title = trim(substr($message, 0, 80));
                $title .= (strlen($message) > 80) ? '...' : '';
                
                $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, 
                        $message, $title);
            }
        }
        
        parent::_write($event);
    }
}