<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Model type for holding information in memory about possible or applied price type filter
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_attribute in Mana_Filters_Block_Filter_Price constructor.
 */
class Mana_Filters_Model_Filter_Decimal
    extends Mage_Catalog_Model_Layer_Filter_Decimal
    implements Mana_Filters_Interface_Filter
{
    /**
     * Prepare text of item label
     *
     * @param   int $range
     * @param   float $value
     * @return  string
     */
    protected function _renderItemLabel($range, $value)
    {
        $range = $this->_getResource()->getRange($value, $range);
        $result = new Varien_Object();
        Mage::dispatchEvent('m_render_price_range', array('range' => $range, 'model' => $this, 'result' => $result));
        if ($result->getLabel()) {
            return $result->getLabel();
        } else {
            $store = Mage::app()->getStore();
            $fromPrice = $store->formatPrice($range['from'], false);
            $toPrice = $store->formatPrice($range['to'], false);

            return Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
        }
    }

    /**
     * Depending on current filter values, returns available filter options from database
     * and additionally whether individual options are selected or not.
     * @return array
     * @see Mage_Catalog_Model_Layer_Filter_Price::_getItemsData()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    protected function _getItemsData()
    {
        /* @var $query Mana_Filters_Model_Query */
        $query = $this->getQuery();

        $range = $this->getRange();
        $dbRanges = $query->getFilterCounts($this->getFilterOptions()->getCode());
        if ($this->_getIsFilterable() == 2) {
            $nonEmptyRanges = $dbRanges;
            $dbRanges = array();
            $from = (int)floor($this->getMinValue() / $range);
            $to = (int)floor($this->getMaxValue() / $range);
            for ($i = $from; $i <= $to; $i++) {
                $dbRanges[$i] = isset($nonEmptyRanges[$i]) ? $nonEmptyRanges[$i] : 0;
            }
        }
        $data = array();

        $selectedIndexes = array();
        foreach ($this->getMSelectedValues() as $selection) {
            if (strpos($selection, ',') !== false) {
                list($index, $range) = explode(',', $selection);
                $selectedIndexes[] = $index;
            }
        }

        foreach ($dbRanges as $index => $count) {
            $isSelected = in_array($index, $selectedIndexes);
            $data[] = array(
                'label' => $this->_renderItemLabel($range, $index),
                'value' => $index . ',' . $range,
                'count' => $count,
                'm_selected' => $isSelected,
                'm_show_selected' => $this->getFilterOptions()->getIsReverse() ? !$isSelected : $isSelected,
            );
        }

        return $data;
    }

    public function getLowestPossibleValue()
    {
        return (int)$this->getMinValue();
    }

    public function getHighestPossibleValue()
    {
        $result = (int)ceil($this->getMaxValue());
        $min = $this->getLowestPossibleValue();

        return $result != $min ? $result : $result + 1;
    }

    #region Mana_Filters_Interface_Filter methods
    /**
     * Returns whether this filter is applied
     *
     * @return bool
     */
    public function isApplied()
    {
        $appliedValues = $this->getMSelectedValues();

        return !empty($appliedValues);
    }

    /**
     * Applies filter values provided in URL to a given product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return void
     */
    public function applyToCollection($collection)
    {
        $this->_applyToCollection($collection);
    }

    /**
     * Returns true if counting should be done on main collection query and false if a separated query should be done
     * Typically it should return false; however there are some cases (like not applied Solr facets) when it should
     * return true.
     *
     * @return bool
     */
    public function isCountedOnMainCollection()
    {
        return false;
    }

    /**
     * Applies counting query to the current collection. The result should be suitable to processCounts() method.
     * Typically, this method should return final result - option id/count pairs for option lists or
     * min/max pair for slider. However, in some cases (like not applied Solr facets) this method returns collection
     * object and later processCounts() extracts actual counts from this collections.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return mixed
     */
    public function countOnCollection($collection)
    {
        return $this->_getResource()->countOnCollection($collection, $this);
    }

    public function getRangeOnCollection($collection)
    {
        list($min, $max) = $this->_getResource()->getMinMaxForCollection($this, $collection);
        return compact('min', 'max');
    }

    /**
     * Returns option id/count pairs for option lists or min/max pair for slider. Typically, this method just returns
     * $counts. However, in some cases (like not applied Solr facets) this method gets a collection object with Solr
     * results and extracts those results.
     *
     * @param mixed $counts
     * @return array
     */
    public function processCounts($counts)
    {
        return $counts;
    }

    /**
     * Returns whether a given filter $modelToBeApplied should be applied when this filter is being counted. Typically,
     * returns true for all filters except this one.
     *
     * @param $modelToBeApplied
     * @return mixed
     */
    public function isFilterAppliedWhenCounting($modelToBeApplied)
    {
        return $modelToBeApplied != $this;
    }

    #endregion
    #region common part for all mana_filters/filter_* models



    /**
     * Creates in-memory representation of a single option of a filter
     * @param array $data
     * @return Mana_Filters_Model_Item
     */
    protected function _createItemEx($data)
    {
        return Mage::getModel('mana_filters/item')
                ->setData($data)
                ->setFilter($this);
    }

    /**
     * Initializes internal array of in-memory representations of options of a filter
     * @return Mana_Filters_Model_Filter_Attribute
     * @see Mage_Catalog_Model_Layer_Filter_Abstract::_initItems()
     */
    protected function _initItems()
    {
        /* @var $ext Mana_Filters_Helper_Extended */
        $ext = Mage::helper(strtolower('Mana_Filters/Extended'));

        $data = $this->_getItemsData();
        $items = array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItemEx($itemData);
        }
        $items = $ext->processFilterItems($this, $items);
        $this->_items = $items;

        return $this;
    }

    /**
     * This method locates resource type which should do all dirty job with the database. In this override, we
     * instruct Magento to take our resource type, not standard.
     * @see Mage_Catalog_Model_Layer_Filter_Attribute::_getResource()
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            /* @var $helper Mana_Filters_Helper_Data */
            $helper = Mage::helper(strtolower('Mana_Filters'));

            $this->_resource = Mage::getResourceModel(
                $helper->getFilterTypeName('resource', $this->getFilterOptions())
            );
        }

        return $this->_resource;
    }

    protected function _getIsFilterable()
    {
        switch ($this->getMode()) {
            case 'category':
                return $this->getFilterOptions()->getIsEnabled();
            case 'search':
                return $this->getFilterOptions()->getIsEnabledInSearch();
            default:
                throw new Exception('Not implemented');
        }
    }

    public function getRemoveUrl()
    {
        $query = array($this->getRequestVar() => $this->getResetValue());
        if ($this->coreHelper()->isManadevDependentFilterInstalled()) {
            $query = $this->dependentHelper()->removeDependentFiltersFromUrl($query, $this->getRequestVar());
        }

        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query'] = $query;

        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    protected function _getIsFilterableAttribute($attribute)
    {
        return $this->_getIsFilterable(); //return $this->getFilterOptions()->getIsEnabled();
    }

    public function getName()
    {
        return $this->getFilterOptions()->getName();
    }

    #endregion
    #region Methods common for Prices and Decimals


    public function getCurrentRangeLowerBound()
    {
        $selections = $this->getMSelectedValues();
        if ($selections && count($selections) == 1) {
            if (strpos($selections[0], ',') !== false) {
                list($index, $range) = explode(',', $selections[0]);

                return $index;
            }
        }

        return $this->getLowestPossibleValue();
    }

    public function getCurrentRangeHigherBound()
    {
        $selections = $this->getMSelectedValues();
        if ($selections && count($selections) == 1) {
            if (strpos($selections[0], ',') !== false) {
                list($index, $range) = explode(',', $selections[0]);

                return $range;
            }
        }

        return $this->getHighestPossibleValue();
    }

    public function getRange()
    {
        $range = $this->getData('range');

        $value = $this->getMSelectedValues();
        if (!empty($value) && strpos($value[0], ',') !== false) {
            list($index, $range) = explode(',', $value[0]);
        }

        if (!$range) {
            if (Mage::helper('mana_db')->hasOverriddenValueEx($this->getFilterOptions(), 24)) {
                $range = (float)$this->getFilterOptions()->getRangeStep();
            } elseif (Mage::helper('mana_db')->hasOverriddenValueEx(
                $this->getFilterOptions(),
                24,
                'global_default_mask'
            )
            ) {
                $range = (float)$this->getFilterOptions()->getGlobalRangeStep();
            }
        }
        if (!$range) {
            $minValue = $this->getMinValue();
            $maxValue = $this->getMaxValue();
            if ($this->hasNoResults()) {
                $range = 1;
                while (ceil(($maxValue - $minValue) / $range) > 10) {
                    $range *= 10;
                }
            }
            else {
                $index = 1;
                do {
                    $range = pow(10, (strlen(floor($maxValue)) - $index));
                    $this->setData('range', $range);
                    /* @var $query Mana_Filters_Model_Query */
                    $query = $this->getQuery();
                    $items = $query->getFilterCounts($this->getFilterOptions()->getCode(), false);
                    $index++;
                } while ($range > self::MIN_RANGE_POWER && count($items) < 2);
            }
        }
        $this->setData('range', $range);
        return $this->getData('range');
    }

    protected $_hasNoResults = false;
    public function hasNoResults() {
        return $this->_hasNoResults;
    }

    protected $_isMinMaxCalculated = false;
    protected $_minMax;

    public function getDecimalMinMax() {
        /* @var $query Mana_Filters_Model_Query */
        $query = $this->getQuery();
        $queryResult = $query->getFilterRange($this->getFilterOptions()->getCode());
        $minMax = $queryResult;
        if (!$minMax['min'] && !($minMax['max']) && $this->_getIsFilterable() == 2) {
            $rootCategory = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load(Mage::app()->getStore()->getRootCategoryId());
            $currentCategory = $this->getLayer()->getCurrentCategory();
            $this->getLayer()->setCurrentCategory($rootCategory);
            $queryResult = $query->getFilterRange($this->getFilterOptions()->getCode(), false,
                $this->getLayer()->getProductCollection(), false);
            $this->getLayer()->setCurrentCategory($currentCategory);
            $minMax = $queryResult;
            $minMax['hasNoResults'] = true;
        }
        return $minMax;
    }

    protected function _calculateMinMax() {
        if (!$this->_isMinMaxCalculated) {
            $this->_minMax = $this->getDecimalMinMax();
            if (!empty($this->_minMax['hasNoResults'])) {
                unset($this->_minMax['hasNoResults']);
                $this->_hasNoResults = true;
            }
            $this->_isMinMaxCalculated = true;
        }
        return $this->_minMax;
    }
    public function getMinValue()
    {
        $result = $this->_calculateMinMax();
        return $result['min'];
    }

    public function getMaxValue() {
        $result = $this->_calculateMinMax();
        return $result['max'];
    }
    public function init() {
    }
    /**
     * Returns all values currently selected for this filter
     */
    public function getMSelectedValues() {
        $values = Mage::helper('mana_core')->sanitizeRequestNumberParam($this->_requestVar, array(
            array('sep' => '_', 'as_string' => true),
            array('sep' => ',', 'as_string' => true)
        ));
        return $values ? explode('_', $values) : array();
    }

    /**
     * Applies filter values provided in URL to a given product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return void
     */
    protected function _applyToCollection($collection)
    {
        $this->_getResource()->applyToCollection($collection, $this, $this->getMSelectedValues());
    }

    /**
     * Adds all selected items of this filters to the layered navigation state object
     *
     * @return void
     */
    public function addToState()
    {
        foreach ($this->getMSelectedValues() as $selection) {
            if (strpos($selection, ',') !== false) {
                list($index, $range) = explode(',', $selection);
                $this->getLayer()->getState()->addFilter(
                    $this->_createItemEx(
                        array(
                            'label' => $this->_renderItemLabel($range, $index),
                            'value' => $selection,
                            'm_selected' => true,
                            'm_show_selected' => $this->getFilterOptions()->getIsReverse(),
                        )
                    )
                );
            }
        }
    }


    public function isUpperBoundInclusive() {
        return $this->_getResource()->isUpperBoundInclusive();
    }
    #endregion
    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return ManaPro_FilterDependent_Helper_Data
     */
    public function dependentHelper() {
        return Mage::helper('manapro_filterdependent');
    }

    #endregion
}