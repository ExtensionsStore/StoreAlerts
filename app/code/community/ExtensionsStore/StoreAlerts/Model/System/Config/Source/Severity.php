<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Model_System_Config_Source_Severity extends Varien_Object 
{

    /**
     * Options array of severity codes
     *
     * @return array
     */
    public function toOptionArray($multiselect = false) {
        
        $options = array();
        
        $severities = Mage::getModel('adminnotification/inbox')->getSeverities();
                
        foreach ($severities as $value => $label){
            
            $options[] = array('value' => $value, 'label' => $label);
        }

        return $options;
    }

}
