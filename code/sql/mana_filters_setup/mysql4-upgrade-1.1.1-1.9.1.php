<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mana_Filters_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->installEntities();
$installer->updateDefaultMaskFields(Mana_Filters_Model_Filter::ENTITY);

$installer->endSetup();