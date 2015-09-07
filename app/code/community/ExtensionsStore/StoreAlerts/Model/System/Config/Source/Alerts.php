<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Model_System_Config_Source_Alerts extends Varien_Object 
{

    /**
     * Options array of alerts
     *
     * @return array
     */
    public function toOptionArray($multiselect = false) {
        
        $alerts = array();
        
        $types = Mage::getModel('storealerts/alert')->getTypes();
        
        $helper = Mage::helper('storealerts');
        
        foreach ($types as $value => $type){
            
            $label = $helper->__($type['label']);
            
            $alerts[] = array('value' => $value, 'label' => $label);
        }

        return $alerts;
    }

}
