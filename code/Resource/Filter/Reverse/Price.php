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
class Mana_Filters_Resource_Filter_Reverse_Price extends Mana_Filters_Resource_Filter_Price {
    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param Mana_Filters_Model_Filter_Price $model
     * @param array $value
     * @return Mana_Filters_Resource_Filter_Price
     */
    public function applyToCollection($collection, $model, $value) {
        $collection->addPriceData($model->getCustomerGroupId(), $model->getWebsiteId());

        $select     = $collection->getSelect();
        $response   = $this->_dispatchPreparePriceEvent($model, $select);

        $table      = $this->_getIndexTableAlias();
        $additional = join('', $response->getAdditionalCalculations());
        $fix = $this->_getConfigurablePriceFix();
        $rate       = $model->getCurrencyRate();
        $precision = 2;//$filter->getDecimalDigits();
        if ($this->isUpperBoundInclusive()) {
            $priceExpr = new Zend_Db_Expr("ROUND(({$table}.min_price {$additional} {$fix}) * {$rate}, $precision)");
        }
        else {
            $priceExpr = new Zend_Db_Expr("({$table}.min_price {$additional} {$fix}) * {$rate}");
        }

        $condition = '';
        foreach ($model->getMSelectedValues() as $selection) {
            if (strpos($selection, ',') !== false) {
                list($index, $range) = explode(',', $selection);
                $range = $this->getPriceRange($index, $range);
                if ($condition != '') $condition .= ' OR ';
                $condition .= '(('.$priceExpr . ' >= '. $range['from'].') '.
                    'AND ('.$priceExpr . ($this->isUpperBoundInclusive() ? ' <= ' : ' < '). $range['to'].'))';
            }
        }

        if ($condition) {
            $select
                ->distinct()
                ->where("NOT ($condition)");
        }
        return $this;
    }

}