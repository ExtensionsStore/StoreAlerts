<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('extensions_store_storealerts_device')} (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `device_token` VARCHAR(255) NOT NULL,
  `access_token` VARCHAR(255) NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('extensions_store_storealerts_preference')} (
  `user_id` INT(11) UNSIGNED NOT NULL,
  `alerts` TEXT NOT NULL,
  `sounds` TEXT NOT NULL,
  `email_alerts` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `slack_hooks` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY ( `user_id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('extensions_store_storealerts_alert')} (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(20) NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `sound` VARCHAR(255) NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `sent` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->run("CREATE TABLE IF NOT EXISTS {$this->getTable('extensions_store_storealerts_exception')} (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file` VARCHAR(20) NOT NULL,
  `content` TEXT NOT NULL,
  `logged` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->endSetup();