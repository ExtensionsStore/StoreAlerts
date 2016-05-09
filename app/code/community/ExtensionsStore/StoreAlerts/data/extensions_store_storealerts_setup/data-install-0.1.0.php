<?php

/**
 * 
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

$installer = $this;
$installer->startSetup();

try {

	$templateCode = ExtensionsStore_StoreAlerts_Model_Alert::TEMPLATE_CODE;
	$templateSubject = ExtensionsStore_StoreAlerts_Model_Alert::TEMPLATE_SUBJECT;
	
	$emailTemplate = Mage::getModel ( 'core/email_template' )->loadByCode ( $templateCode );
	$emailTemplate->setTemplateCode ( $templateCode );
	$emailTemplate->setTemplateText ( 'Alert Type: {{var type}}
Date Time: {{var datetime}}
Title: {{var title}}
Message: {{var message}}

' );
	$emailTemplate->setTemplateType ( 1 );
	$emailTemplate->setTemplateSubject ( $templateSubject );
	$emailTemplate->save ();

} catch ( Exception $e ) {
    Mage::log($e->getMessage(),Zend_Log::ERR,'extensions_store_storealerts.log');
}


$installer->endSetup();
