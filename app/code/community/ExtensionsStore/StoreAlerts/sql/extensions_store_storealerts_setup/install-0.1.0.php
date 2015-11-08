<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('extensions_store_storealerts_device')} (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `device_token` VARCHAR(255) NOT NULL,
  `access_token` VARCHAR(255) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `alerts` TEXT NOT NULL,
  `sounds` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('extensions_store_storealerts_alert')} (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(20) NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  `sound` VARCHAR(255) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `sent` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->endSetup();