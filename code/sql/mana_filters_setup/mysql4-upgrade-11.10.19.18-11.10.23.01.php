<?php 

/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_filter2_value';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `id` bigint NOT NULL AUTO_INCREMENT,
	  `default_mask0` int unsigned NOT NULL default '0',
	  `edit_session_id` bigint NOT NULL default '0',
	  `edit_status` bigint NOT NULL default '0',
	  

	  `filter_id` bigint NOT NULL,
	  `option_id` int(10) unsigned NOT NULL,
	  `value_id` int(10) unsigned NULL,

	  `name` varchar(255) NOT NULL default '',
	  `position` smallint(5) NOT NULL default '0',
	  
	  PRIMARY KEY  (`id`),
	  KEY `filter_id` (`filter_id`),
	  KEY `option_id` (`option_id`),
	  KEY `value_id` (`value_id`),
	  KEY `edit_session_id` (`edit_session_id`),
	  KEY `edit_status` (`edit_status`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	
	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_db/edit_session` FOREIGN KEY (`edit_session_id`) 
	  REFERENCES `{$installer->getTable('mana_db/edit_session')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_filters/filter2` FOREIGN KEY (`filter_id`) 
	  REFERENCES `{$installer->getTable('mana_filters/filter2')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
	
	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_eav/attribute_option` FOREIGN KEY (`option_id`) 
	  REFERENCES `{$installer->getTable('eav/attribute_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_eav/attribute_option_value` FOREIGN KEY (`value_id`) 
	  REFERENCES `{$installer->getTable('eav/attribute_option_value')}` (`value_id`) ON DELETE SET NULL ON UPDATE SET NULL;

");

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_filter2_value_store';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `id` bigint NOT NULL AUTO_INCREMENT,
	  `global_id` bigint NOT NULL,
	  `store_id` smallint(5) unsigned NOT NULL, 
	  `default_mask0` int unsigned NOT NULL default '0',
	  `edit_session_id` bigint NOT NULL default '0',
	  `edit_status` bigint NOT NULL default '0',
	  
	  `filter_id` bigint NOT NULL,
	  `option_id` int(10) unsigned NOT NULL,
	  `value_id` int(10) unsigned NULL,

	  `name` varchar(255) NOT NULL default '',
	  `position` smallint(5) NOT NULL default '0',
	  
	  PRIMARY KEY  (`id`),
	  KEY `filter_id` (`filter_id`),
	  KEY `option_id` (`option_id`),
	  KEY `value_id` (`value_id`),
	  KEY `edit_session_id` (`edit_session_id`),
	  KEY `edit_status` (`edit_status`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	
	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_db/edit_session` FOREIGN KEY (`edit_session_id`) 
	  REFERENCES `{$installer->getTable('mana_db/edit_session')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
	
	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_filters/filter2_store` FOREIGN KEY (`filter_id`) 
	  REFERENCES `{$installer->getTable('mana_filters/filter2_store')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
	
	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_eav/attribute_option_value` FOREIGN KEY (`value_id`) 
	  REFERENCES `{$installer->getTable('eav/attribute_option_value')}` (`value_id`) ON DELETE SET NULL ON UPDATE SET NULL;
");

$installer->endSetup();

if (!Mage::registry('m_run_db_replication')) {
	Mage::register('m_run_db_replication', true);
}
