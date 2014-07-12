<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Resource type which contains sql code for applying filters and related operations
 * @author Mana Team
 * Injected instead of standard resource catalog/layer_filter_attribute in 
 * Mana_Filters_Model_Filter_Price::_getResource().
 */
class Mana_Filters_Resource_Filter_Reverse_Decimal extends Mana_Filters_Resource_Filter_Decimal {
    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param Mana_Filters_Model_Filter_Decimal $model
     * @param array $value
     * @return Mana_Filters_Resource_Filter_Decimal
     */
    public function applyToCollection($collection, $model, $value) {
        $attribute  = $model->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId())
        );

        $condition = '';
        foreach ($value as $selection) {
            if (strpos($selection, ',') !== false) {
                list($index, $range) = explode(',', $selection);
                $range = $this->getRange($index, $range);
                if ($condition != '') $condition .= ' OR ';
                $condition .= '(('."{$tableAlias}.value" . ' >= '. $range['from'].') '.
                    'AND ('."{$tableAlias}.value" . ($this->isUpperBoundInclusive() ? ' <= ' : ' < '). $range['to'].'))';
            }
        }

        if ($condition) {
            $collection->getSelect()
                ->join(
                    array($tableAlias => $this->getMainTable()),
                    join(' AND ', $conditions),
                    array()
                )
                ->distinct()
                ->where("NOT ($condition)");
        }

        return $this;
    }

}