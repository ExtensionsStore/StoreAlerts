<?php

/**
 * System configuration form button
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);
		$url = $this->getUrl('adminhtml/system_storealerts/testalert');
		$script = 
"<script>
function testAlert(){
    var message = $('extensions_store_storealerts_configuration_test_message').value;
    var data = { message : message };
    new Ajax.Request('".$url."', {
        method: 'POST',
        parameters: data,
        onFailure: function(transport) {
            alert('An error occurred.');
	   },
        onSuccess: function(transport) {
            alert('Test alerts have been sent.');
	   }
    });
}
</script>";
		$html = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('scalable')
			->setLabel('Send Alert')
			->setOnClick("testAlert()")
			->toHtml();
		$html .= $script;

		return $html;
	}
}
