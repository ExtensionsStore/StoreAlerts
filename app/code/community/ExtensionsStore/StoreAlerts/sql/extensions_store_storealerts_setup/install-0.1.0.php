<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_
 * @author      Extensions Store <admin@extensions-store.com>
 */

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('extensions_store_storealerts_device')} (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `device_token` VARCHAR(255) NOT NULL,
  `access_token` VARCHAR(255) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->endSetup();