<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mage_Catalog_Model_Layer getLayer()
 * @method Mana_Filters_Model_Query setLayer(Mage_Catalog_Model_Layer $value)
 */
class Mana_Filters_Model_Query extends Varien_Object
{
    protected $_isInitialized = false;
    protected $_isApplied = false;
    /**
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected $_productCollection;
    /**
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected $_productCollectionPrototype;
    /**
     * @var Varien_Db_Select
     */
    protected $_selectPrototype;
    protected $_filters = array();

    public function init() {
        if (!$this->_isInitialized) {
            $this->_init();
            $this->_isInitialized = true;
        }
        return $this;
    }
    protected function _init() {
        //Mage::log('---', Zend_Log::DEBUG, 'performance.log');
        $this->_productCollection = $this->getLayer()->getProductCollection();
        $this->_productCollectionPrototype = clone $this->_productCollection;
        $this->_selectPrototype = clone $this->_productCollection->getSelect();
    }
    public function getProductCollection() {
        return $this->_productCollection;
    }
    public function createProductCollection() {
        $result = clone $this->_productCollectionPrototype;
        $this->_copyParts($result->getSelect(), $this->_selectPrototype);

        return $result;
    }
    protected function _copyParts($target, $source) {
        foreach (array(Varien_Db_Select::DISTINCT, Varien_Db_Select::COLUMNS, Varien_Db_Select::UNION,
            Varien_Db_Select::FROM, Varien_Db_Select::WHERE, Varien_Db_Select::GROUP, Varien_Db_Select::HAVING,
            Varien_Db_Select::ORDER, Varien_Db_Select::LIMIT_COUNT, Varien_Db_Select::LIMIT_OFFSET,
            Varien_Db_Select::FOR_UPDATE) as $part)
        {
            $target->setPart($part, $source->getPart($part));
        }
    }
    public function addFilter($code, Mana_Filters_Interface_Filter $model) {
        $isApplied = $model->isApplied();
        $this->_filters[$code] = array('model' => $model, 'isApplied' => $isApplied, 'isApplyProcessed' => false);
    }

    public function getFilters() {
        return $this->_filters;
    }

    public function apply() {
        foreach ($this->_filters as $code => $filter) {
            if (!$filter['isApplyProcessed']) {
                $model = $filter['model'];
                /* @var $model Mana_Filters_Interface_Filter */
                $model->init();
                if ($filter['isApplied']) {
                    $model->applyToCollection($this->getProductCollection());
                    $model->addToState();
                }
                if ($isCounted = $model->isCountedOnMainCollection()) {
                    $counts = $model->countOnCollection($this->getProductCollection());
                }
                else {
                    $counts = null;
                }
                $this->_filters[$code]['isCounted'] = $isCounted;
                $this->_filters[$code]['counts'] = $counts;
                $this->_filters[$code]['isApplyProcessed'] = true;
            }
        }
        return $this;
    }

    protected function _apply() {
    }

    /**
     * @param string $code
     * @param bool $cache
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $originalCollection
     * @param bool $applyFilters
     * @return mixed
     */
    public function getFilterRange($code, $cache = true, $originalCollection = null, $applyFilters = true) {
        $currentFilter = $this->_filters[$code];
        /* @var $currentFilterModel Mana_Filters_Interface_Filter */
        $currentFilterModel = $currentFilter['model'];

        if (!$cache || !isset($currentFilter['processedRange'])) {
            if ($originalCollection) {
                $preservedSelect = clone $originalCollection->getSelect();
                $collection = clone $originalCollection;
            }
            else {
                $preservedSelect = clone $this->_productCollection->getSelect();
                $collection = $this->createProductCollection($originalCollection);
            }
            if ($applyFilters) {
                foreach ($this->_filters as $filter) {
                    /* @var $filterModel Mana_Filters_Interface_Filter */
                    $filterModel = $filter['model'];

                    if ($filter['isApplied'] && $currentFilterModel->isFilterAppliedWhenCounting($filterModel)) {
                        $filterModel->applyToCollection($collection);
                    }
                }
            }

            $result = $currentFilterModel->getRangeOnCollection($collection);
            if ($cache) {
                $currentFilter['processedRange'] = $result;
            }

            if ($originalCollection) {
                $this->_copyParts($originalCollection->getSelect(), $preservedSelect);
            }
            else {
                $this->_copyParts($this->_productCollection->getSelect(), $preservedSelect);
            }
        }
        else {
            $result = $currentFilter['processedRange'];
        }
        $this->_filters[$code] = $currentFilter;

        return $result;
    }
    public function getFilterCounts($code, $cache = true) {
        $currentFilter = $this->_filters[$code];
        /* @var $currentFilterModel Mana_Filters_Interface_Filter */
        $currentFilterModel = $currentFilter['model'];

        if (!$cache || !isset($currentFilter['processedCounts'])) {
            if (!empty($currentFilter['isCounted'])) {
                $currentFilter['processedCounts'] = $currentFilterModel->processCounts($currentFilter['counts']);
            }
            else {
                $mainSelect = clone $this->_productCollection->getSelect();

                $collection = $this->createProductCollection();
                //$sql = $collection->getSelect()->__toString();
                foreach ($this->_filters as $filter) {
                    /* @var $filterModel Mana_Filters_Interface_Filter */
                    $filterModel = $filter['model'];

                    if ($filter['isApplied'] && $currentFilterModel->isFilterAppliedWhenCounting($filterModel)) {
                        $filterModel->applyToCollection($collection);
                    }
                }

                $counts = $currentFilterModel->countOnCollection($collection);
                $currentFilter['processedCounts'] = $currentFilterModel->processCounts($counts);

                $this->_copyParts($this->_productCollection->getSelect(), $mainSelect);
            }
            if ($cache) {
                $this->_filters[$code] = $currentFilter;
            }
        }
        return $currentFilter['processedCounts'];
    }

    #region Dependencies

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filtersHelper() {
        return Mage::helper('mana_filters');
    }

    #endregion
}