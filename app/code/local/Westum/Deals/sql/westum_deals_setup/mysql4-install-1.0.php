<?php

$this->startSetup();

$this->run("
    
 
CREATE TABLE IF NOT EXISTS {$this->getTable('westum_deals/deals_store')} (
  `item_id` int(10) unsigned NOT NULL auto_increment,
  `deal_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,  
  PRIMARY KEY  (`item_id`),
  KEY `FK_MAGALTER_DEAL_STORE` (`deal_id`),
  CONSTRAINT `FK_MAGALTER_DEAL_STORE` FOREIGN KEY (`deal_id`) REFERENCES {$this->getTable('westum_deals/deals')} (`deal_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
   
   
 CREATE TABLE IF NOT EXISTS {$this->getTable('westum_deals/deals')} (
  `deal_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,  
  `status` tinyint(4) NOT NULL, 
  `priority` int(10) NOT NULL,
  `groups` varchar(255) default NULL,
  `categories` text default NULL,
  `positions` varchar(255) default NULL,
  `available_from` datetime DEFAULT NULL,
  `available_to` datetime DEFAULT NULL,
  `price` decimal(12,4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `additional_settings` text NOT NULL,
  `design_package` varchar(255) default NULL,
  `new_email` tinyint(4) NOT NULL DEFAULT '0',
  `close_email` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`deal_id`),
  KEY `KEY_MAGALTER_PRODUCT_ID` (`product_id`),
  KEY `KEY_MAGALTER_PRIORITY` (`priority`),
  KEY `KEY_MAGALTER_N_EMAIL` (`new_email`),
  KEY `KEY_MAGALTER_S_EMAIL` (`close_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
"); 
 
/*
 *
DROP TABLE IF EXISTS westum_deals_store;
DROP TABLE IF EXISTS westum_deals;
delete from core_resource where code = 'westum_deals_setup'
*/
 
$this->endSetup();