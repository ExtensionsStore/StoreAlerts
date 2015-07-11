<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Test_Controller_IndexController extends EcomDev_PHPUnit_Test_Case_Controller {

    
    /**
     * @test
     * @loadFixture
     */
    public function registerAction() {

        echo "\nStarting ExtensionsStore_StoreAlerts controller test.";
        
        $_SERVER['HTTP_HOST'] = 'ce-1.9.1.local';
        $_SERVER['HTTPS'] = 'on';
        
        $this->getRequest()->setMethod('POST')
            ->setPost(array(
                'device_token' => 'ec86eae37d8513a8a4005e255fa9ecc44a4bc572c7834d26866e11c795877d88',
                'username' => 'owner@example.com',
                'password' => 'testtesttest'
            ));
        

        $this->dispatch('storealerts/index/register');
        
        $this->assertRequestRoute('storealerts/index/register');  
        
        $this->assertResponseBodyJson();
        
        $result = array();
        $result['error'] = false;
        $result['data'] = array(
                'key' => '229083104fef36de8ae47028cfc06f93',
                'secret' => '01837b8da31cff5060e70123baee6b20',
                'callback_url' => 'http://ce-1.9.1.local/'
            );        
        
        $this->assertResponseBodyJsonMatch($result);

        echo "\nCompleted ExtensionsStore_StoreAlerts controller test.";        
        
    }

}
