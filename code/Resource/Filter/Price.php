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
class Mana_Filters_Resource_Filter_Price extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price {
    protected function _getSelectOnCollection($collection, $filter)
    {
        if (Mage::helper('mana_core')->isMageVersionEqualOrGreater('1.7')) {
            return $this->_getSelectOnCollection_1_7($collection, $filter);
        }
        else {
            return $this->_getSelectOnCollection_old($collection, $filter);
        }
    }
    protected function _getSelectOnCollection_1_7($collection, $filter)
    {
        $collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

        $select = clone $collection->getSelect();

        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        // remove join with main table
        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS])
            || !isset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS])
        ) {
            return $select;
        }

        // processing FROM part
        $priceIndexJoinPart = $fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS];
        $priceIndexJoinConditions = explode('AND', $priceIndexJoinPart['joinCondition']);
        $priceIndexJoinPart['joinType'] = Zend_Db_Select::FROM;
        $priceIndexJoinPart['joinCondition'] = null;
        $fromPart[Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;
        unset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS]);
        $select->setPart(Zend_Db_Select::FROM, $fromPart);
        foreach ($fromPart as $key => $fromJoinItem) {
            $fromPart[$key]['joinCondition'] = $this->_replaceTableAlias($fromJoinItem['joinCondition']);
        }
        $select->setPart(Zend_Db_Select::FROM, $fromPart);

        // processing WHERE part
        $wherePart = $select->getPart(Zend_Db_Select::WHERE);
        foreach ($wherePart as $key => $wherePartItem) {
            $wherePart[$key] = $this->_replaceTableAlias($wherePartItem);
        }
        $select->setPart(Zend_Db_Select::WHERE, $wherePart);
        $excludeJoinPart = Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS . '.entity_id';
        foreach ($priceIndexJoinConditions as $condition) {
            if (strpos($condition, $excludeJoinPart) !== false) {
                continue;
            }
            $select->where($this->_replaceTableAlias($condition));
        }
        $select->where($this->_getPriceExpression($filter, $select) . ' IS NOT NULL');

        return $select;
    }

    protected function _getSelectOnCollection_old($collection, $filter)
    {
        $collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

        $select = clone $collection->getSelect();

        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        return $select;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param Mana_Filters_Model_Filter_Price $model
     * @return Mana_Filters_Resource_Filter_Price
     */
    public function countOnCollection($collection, $model) {
        if (Mage::helper('mana_core')->isMageVersionEqualOrGreater('1.7')) {
            $table = Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS;
            $select = $this->_getSelectOnCollection($collection, $model);
            $rate = $model->getCurrencyRate();
            $priceExpression = "({$this->_getPriceExpression($model, $select)}) * {$rate}";

            $range = floatval($model->getPriceRange());
            if ($range == 0) {
                $range = 1;
            }
            $countExpr = new Zend_Db_Expr('COUNT(*)');
            $rangeExpr = new Zend_Db_Expr("FLOOR(({$priceExpression}) / {$range}) + 1");

            $select->columns(array(
                'range' => $rangeExpr,
                'count' => $countExpr
            ));
            $select->group($rangeExpr)->order("$rangeExpr ASC");

            Mage::helper('mana_filters')->resetProductCollectionWhereClause($select);
            $select->where("{$table}.min_price > 0");

//Mage::log($select->__toString(), Zend_Log::DEBUG, 'price.log' );
//Mage::log(json_encode($this->_getReadAdapter()->fetchPairs($select)), Zend_Log::DEBUG, 'price.log' );
            return $this->_getReadAdapter()->fetchPairs($select);
        }
        else {
            $select = $this->_getSelectOnCollection($collection, $model);
            $connection = $this->_getReadAdapter();
            $response = $this->_dispatchPreparePriceEvent($model, $select);
            $table = $this->_getIndexTableAlias();
            $additional = join('', $response->getAdditionalCalculations());
            $fix = $this->_getConfigurablePriceFix();
            $rate = $model->getCurrencyRate();
            $countExpr = new Zend_Db_Expr('COUNT(DISTINCT e.entity_id)');
            $rangeExpr = new Zend_Db_Expr("FLOOR((({$table}.min_price {$additional} {$fix}) * {$rate}) / {$model->getPriceRange()}) + 1");

            $select->columns(array(
                'range' => $rangeExpr,
                'count' => $countExpr
            ));

            // MANA BEGIN: make sure price filter is not applied
            Mage::helper('mana_filters')->resetProductCollectionWhereClause($select);
            // MANA END

            $select->where("{$table}.min_price > 0");
            $select->group('range');

            return $connection->fetchPairs($select);
        }
    }

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
                ->where($condition);
        }
        return $this;
    }



    public function isUpperBoundInclusive() {
        return false;
    }
    /**
     * Retrieve maximal price for attribute
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return float
     */
    public function getMaxPriceOnCollection($filter, $collection)
    {
        $select     = $this->_getSelectOnCollection($collection, $filter);
        $connection = $this->_getReadAdapter();

        if (Mage::helper('mana_core')->isMageVersionEqualOrGreater('1.7')) {
            $maxPriceExpr = new Zend_Db_Expr("MAX({$this->_getPriceExpression($filter, $select)}) AS m_max_price");
        }
        else {
            $response = $this->_dispatchPreparePriceEvent($filter, $select);
            $table = $this->_getIndexTableAlias();
            $additional = join('', $response->getAdditionalCalculations());
            $fix = $this->_getConfigurablePriceFix();
            $maxPriceExpr = new Zend_Db_Expr("MAX({$table}.min_price {$additional} {$fix}) AS m_max_price");
        }

        //Mage::helper('mana_filters')->resetProductCollectionWhereClause($select);
        $select->columns(array($maxPriceExpr))->order('m_max_price DESC');

        $result  = $connection->fetchOne($select) * $filter->getCurrencyRate();
//        Mage::log('MAX select: ' . ((string)$select), Zend_Log::DEBUG, 'price.log');
//        Mage::log("MAX result: $result", Zend_Log::DEBUG, 'price.log');
//        Mage::log('LIST select: '. (string)$filter->getLayer()->getProductCollection()->getSelect(), Zend_Log::DEBUG, 'price.log');
//        $this->getCount($filter, 1);
        return $result;
    }

    public function getPriceRange($index, $range) {
    	return array('from' => $range * ($index - 1), 'to' => $range * $index);
    }

    protected function _getConfigurablePriceFix() {
        if (!Mage::getStoreConfigFlag('mana_filters/general/adjust_configurable_price')) {
            return '';
        }
        /* @var $db Mage_Core_Model_Resource */ $db = Mage::getSingleton('core/resource');
        $request = Mage::app()->getRequest();
        $subSelect = '';

        $values = array();
        foreach (Mage::helper('mana_filters')->getFilterOptionsCollection() as $filter) {
            if ($filter->getType() == 'attribute' && ($param = $request->getParam($filter->getCode()))) {
                $values = array_merge($values, Mage::helper('mana_core')->sanitizeNumber($param, array('_')));
            }
        }
        if (count($values) > 0) {
            $values = implode(',', $values);
            $subSelect = "SELECT SUM(super_price.pricing_value) ".
                "FROM {$db->getTableName('catalog/product_super_attribute')} AS super ".
                "INNER JOIN {$db->getTableName('catalog/product_super_attribute_pricing')} AS super_price ".
                    "ON super.product_super_attribute_id = super_price.product_super_attribute_id AND ".
                        "super_price.is_percent = 0 AND super_price.value_index IN ($values) ".
                "WHERE super.product_id = e.entity_id";
        }
        return $subSelect ? " + COALESCE(($subSelect), 0)" : '';
    }

    protected $_preparedExpressions = array();

    protected function _getPriceExpression($filter, $select, $replaceAlias = true) {
        foreach ($this->_preparedExpressions as $expr) {
            if ($expr['select'] == $select) {
                return $expr['result'];
            }
        }

        $response = new Varien_Object();
        $response->setAdditionalCalculations(array());
        $tableAliases = array_keys($select->getPart(Zend_Db_Select::FROM));
        if (in_array(Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS, $tableAliases)) {
            $table = Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS;
        }
        else {
            $table = reset($tableAliases);
        }

        // prepare event arguments
        $eventArgs = array(
            'select' => $select,
            'table' => $table,
            'store_id' => $filter->getStoreId(),
            'response_object' => $response
        );

        Mage::dispatchEvent('catalog_prepare_price_select', $eventArgs);

        $table = Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS;
        $additional = $this->_replaceTableAlias(join('', $response->getAdditionalCalculations()));

        $fix = $this->_getConfigurablePriceFix();
        $result = "{$table}.min_price {$additional} {$fix}";

        if ($replaceAlias) {
            $result = $this->_replaceTableAlias($result);
        }

        $this->_preparedExpressions[] = compact('select', 'result');

        return $result;
    }

}