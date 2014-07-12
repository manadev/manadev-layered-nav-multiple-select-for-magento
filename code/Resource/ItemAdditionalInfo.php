<?php
/** 
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Filters_Resource_ItemAdditionalInfo extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * @param Varien_Db_Select $select
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @return mixed
     */
    abstract public function selectItems($select, $filter);

    /**
     * @param Varien_Db_Select $select
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return mixed
     */
    abstract public function countItems($select, $filter, $collection);

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('catalog');
    }
}