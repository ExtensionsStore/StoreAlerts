<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Model_System_Config_Source_Loglevel extends Varien_Object 
{

    /**
     * Options array of severity codes
     *
     * @return array
     */
    public function toOptionArray($multiselect = false) {
        
        $options = array(
        		
        		array('value' => Zend_Log::EMERG, 'label' => '0 - Emergency: system is unusable'),
        		array('value' => Zend_Log::ALERT, 'label' => '1 - Alert: action must be taken immediately'),
        		array('value' => Zend_Log::CRIT, 'label' => '2 - Critical: critical conditions'),
        		array('value' => Zend_Log::ERR, 'label' => '3 - Error: error conditions'),
        		array('value' => Zend_Log::WARN, 'label' => '4 - Warning: warning conditions'),
        		array('value' => Zend_Log::NOTICE, 'label' => '5 - Notice: normal but significant condition'),
        		array('value' => Zend_Log::INFO, 'label' => '6 - Informational: informational messages'),
        		array('value' => Zend_Log::DEBUG, 'label' => '7 - Debug: debug messages'),
        );

        return $options;
    }

}
