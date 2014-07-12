<?php 

/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}

foreach (array('mana_filters/filter2', 'mana_filters/filter2_store') as $table) {
    $installer->run("
        ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
            `disable_no_result_options` tinyint NOT NULL default '1'
        );
    ");
}

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

if (!Mage::registry('m_run_db_replication')) {
    Mage::register('m_run_db_replication', true);
}
