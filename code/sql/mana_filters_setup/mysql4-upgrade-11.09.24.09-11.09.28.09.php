<?php 

/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_filter2';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `id` bigint NOT NULL AUTO_INCREMENT,
	  `default_mask0` int unsigned NOT NULL default '0',
	  `code` varchar(255) NOT NULL,
	  `type` varchar(20) NOT NULL,

	  `is_enabled` tinyint NOT NULL default '0',
	  `display` varchar(255) NOT NULL default '',
	  `name` varchar(255) NOT NULL default '',
	  `is_enabled_in_search` tinyint NOT NULL default '0',
	  `position` int NOT NULL default '0',
	  
	  PRIMARY KEY  (`id`),
	  UNIQUE KEY `code` (`code`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	
");

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_filter2_store';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `id` bigint NOT NULL AUTO_INCREMENT,
	  `default_mask0` int unsigned NOT NULL default '0',
	  `global_id` bigint NOT NULL,
	  `store_id` smallint(5) unsigned NOT NULL, 
	   
	  `is_enabled` tinyint NOT NULL default '0',
	  `display` varchar(255) NOT NULL default '',
	  `name` varchar(255) NOT NULL default '',
	  `is_enabled_in_search` tinyint NOT NULL default '0',
	  `position` int NOT NULL default '0',
	  
	  PRIMARY KEY  (`id`),
	  KEY `global_id` (`global_id`),
	  KEY `store_id` (`store_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	
	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_filters/filter2` FOREIGN KEY (`global_id`) 
	  REFERENCES `{$installer->getTable('mana_filters/filter2')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_core/store` FOREIGN KEY (`store_id`) 
	  REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$installer->endSetup();

if (!Mage::registry('m_run_db_replication')) {
	Mage::register('m_run_db_replication', true);
}
