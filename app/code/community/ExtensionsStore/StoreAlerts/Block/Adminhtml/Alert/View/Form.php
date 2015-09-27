<?php

/**
 * View alert form
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_StoreAlerts_Block_Adminhtml_Alert_View_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'view_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $alert = Mage::registry('alert');

        if ($alert->getId()) {
            $form->addField('alert_id', 'hidden', array(
                'name' => 'id',
            ));
            $form->setValues($alert->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}