<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Model type for holding information in memory about possible or applied price filter
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_attribute in Mana_Filters_Block_Filter_Price constructor.
 */
class Mana_Filters_Model_Filter_Price
    extends Mage_Catalog_Model_Layer_Filter_Price
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
        $range = $this->_getResource()->getPriceRange($value, $range);
        $result = new Varien_Object();
        Mage::dispatchEvent('m_render_price_range', array('range' => $range, 'model' => $this, 'result' => $result));
        if ($result->getLabel()) {
            return $result->getLabel();
        }
        else {
            $store      = Mage::app()->getStore();
            $fromPrice  = $store->formatPrice($range['from'], false);
            $toPrice    = $store->formatPrice($range['to'], false);
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

        $range = $this->getPriceRange();
        $dbRanges = $query->getFilterCounts($this->getFilterOptions()->getCode());
        if ($this->_getIsFilterable() == 2) {
            $nonEmptyRanges = $dbRanges;
            $dbRanges = array();
            for ($i = 1; ($i - 1) * $range < $this->getMaxPriceInt(); $i++) {
                $dbRanges[$i]  = isset($nonEmptyRanges[$i]) ? $nonEmptyRanges[$i] : 0;
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

	public function getLowestPossibleValue() {
		return 0;
	}

    public function getHighestPossibleValue()
    {
        return $this->getMaxPriceInt();
    }

    protected $_hasNoResults = false;
    public function hasNoResults() {
        return $this->_hasNoResults;
    }

    protected $_isMaxPriceIntCalculated = false;
    protected $_maxPriceInt;
    public function getMaxPriceInt() {
        if (!$this->_isMaxPriceIntCalculated) {
            /* @var $query Mana_Filters_Model_Query */
            $query = $this->getQuery();
            $queryResult = $query->getFilterRange($this->getFilterOptions()->getCode());
            $this->_maxPriceInt = $queryResult['max'];
            if (!$this->_maxPriceInt && $this->_getIsFilterable() == 2) {
                $rootCategory = Mage::getModel('catalog/category')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load(Mage::app()->getStore()->getRootCategoryId());
                $currentCategory = $this->getLayer()->getCurrentCategory();
                if ($rootCategory->getId() != $currentCategory->getId()) {
                    $this->getLayer()->setCurrentCategory($rootCategory);
                    $queryResult = $query->getFilterRange($this->getFilterOptions()->getCode(), false,
                        $this->getLayer()->getProductCollection(), false);
                    $this->getLayer()->setCurrentCategory($currentCategory);
                }
                else {
                    $queryResult = $query->getFilterRange($this->getFilterOptions()->getCode(), false,
                        $query->createProductCollection(), false);
                }
                $this->_maxPriceInt = $queryResult['max'];
                $this->_hasNoResults = true;
            }
            $this->_isMaxPriceIntCalculated = true;
        }
        return $this->_maxPriceInt;
    }
    public function getDecimalDigits() {
        return 0;
    }

    #region Mana_Filters_Interface_Filter methods
    /**
     * Returns whether this filter is applied
     *
     * @return bool
     */
    public function isApplied() {
        $appliedValues = $this->getMSelectedValues();

        return !empty($appliedValues);
    }
    /**
     * Applies filter values provided in URL to a given product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return void
     */
    public function applyToCollection($collection) {
        $this->_getResource()->applyToCollection($collection, $this, $this->getMSelectedValues());
    }
    /**
     * Returns true if counting should be done on main collection query and false if a separated query should be done
     * Typically it should return false; however there are some cases (like not applied Solr facets) when it should
     * return true.
     *
     * @return bool
     */
    public function isCountedOnMainCollection() {
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
    public function countOnCollection($collection) {
        $counts = $this->_getResource()->countOnCollection($collection, $this);
        return $counts;
    }

    public function getRangeOnCollection($collection)
    {
        $min = 0;
        $max = $this->_getResource()->getMaxPriceOnCollection($this, $collection);
        $max = $this->_ceil($max);
        $this->setData('max_price_int', $max);
        return compact('min', 'max');
    }

    protected function _ceil($value) {
        return ceil($value);
    }

    /**
     * Returns option id/count pairs for option lists or min/max pair for slider. Typically, this method just returns
     * $counts. However, in some cases (like not applied Solr facets) this method gets a collection object with Solr
     * results and extracts those results.
     *
     * @param mixed $counts
     * @return array
     */
    public function processCounts($counts) {
        return $counts;
    }
    /**
     * Returns whether a given filter $modelToBeApplied should be applied when this filter is being counted. Typically,
     * returns true for all filters except this one.
     *
     * @param $modelToBeApplied
     * @return mixed
     */
    public function isFilterAppliedWhenCounting($modelToBeApplied) {
        return $modelToBeApplied != $this;
    }
    #endregion

    #region common part for all mana_filters/filter_* models
    /**
     * Creates in-memory representation of a single option of a filter
     * @param array $data
     * @return Mana_Filters_Model_Item
     * This method is cloned from method _createItem() in parent class (method body was pasted from parent class
     * completely rewritten.
     * Standard method did not give us possibility to initialize non-standard fields.
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
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items=array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItemEx($itemData);
        }
    	/* @var $ext Mana_Filters_Helper_Extended */ $ext = Mage::helper(strtolower('Mana_Filters/Extended'));
        $items = $ext->processFilterItems($this, $items);
        // MANA END
        $this->_items = $items;
        return $this;
    }
    /**
     * This method locates resource type which should do all dirty job with the database. In this override, we
     * instruct Magento to take our resource type, not standard.
     * @see Mage_Catalog_Model_Layer_Filter_Price::_getResource()
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            /* @var $helper Mana_Filters_Helper_Data */
            $helper = Mage::helper(strtolower('Mana_Filters'));

            $this->_resource = Mage::getResourceModel($helper->getFilterTypeName('resource', $this->getFilterOptions()));
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

    public function getCurrentRangeLowerBound() {
		$selections = $this->getMSelectedValues();
		if ($selections && count($selections) == 1) {
            if (strpos($selections[0], ',') !== false) {
                list($index, $range) = explode(',', $selections[0]);
        	    return $index;
            }
		}
    	return $this->getLowestPossibleValue();
	}
	public function getCurrentRangeHigherBound() {
		$selections = $this->getMSelectedValues();
		if ($selections && count($selections) == 1) {
            if (strpos($selections[0], ',') !== false) {
                list($index, $range) = explode(',', $selections[0]);
        	    return $range;
            }
		}
        return $this->getHighestPossibleValue();
	}

    public function getPriceRange() {
        if (!$this->getData('price_range_set')) {
            $this->setData('price_range_set', true);
            $range = $this->getData('price_range');

            $value = $this->getMSelectedValues();
            if (!empty($value) && strpos($value[0], ',') !== false) {
                list($index, $range) = explode(',', $value[0]);
            }

            if (!$range) {
                if (Mage::helper('mana_db')->hasOverriddenValueEx($this->getFilterOptions(), 24)) {
                    $range = (float)$this->getFilterOptions()->getRangeStep();
                }
                elseif (Mage::helper('mana_db')->hasOverriddenValueEx($this->getFilterOptions(), 24, 'global_default_mask')) {
                    $range = (float)$this->getFilterOptions()->getGlobalRangeStep();
                }
            }
            if (!$range) {
                $currentCategory = Mage::registry('current_category_filter');
                /* @var $currentOptionPage Mana_AttributePage_Model_OptionPage_Store */
                $currentOptionPage = Mage::registry('current_option_page');

                if ($currentCategory) {
                    $range = $currentCategory->getFilterPriceRange();
                }
                elseif ($currentOptionPage && $currentOptionPage->getData('price_step')) {
                    $range = $currentOptionPage->getData('price_step');
                }
                else {
                    $range = $this->getLayer()->getCurrentCategory()->getFilterPriceRange();
                }

                $maxPrice = $this->getMaxPriceInt();
                if (!$range) {
                    $calculation = Mage::app()->getStore()->getConfig('catalog/layered_navigation/price_range_calculation');
                    if (!$calculation) {
                        $calculation = 'auto';
                    }
                    if ($calculation == 'auto') {
                        if ($this->hasNoResults()) {
                            $range = 1;
                            while (ceil($maxPrice / $range) > 10) {
                                $range *= 10;
                            }
                        }
                        else {
                            $index = 1;
                            do {
                                $range = pow(10, (strlen(floor($maxPrice)) - $index));
                                $this->setData('price_range', $range);
                                /* @var $query Mana_Filters_Model_Query */
                                $query = $this->getQuery();
                                $items = $query->getFilterCounts($this->getFilterOptions()->getCode(), false);
                                $index++;
                            } while ($range > self::MIN_RANGE_POWER && count($items) < 2);


                            while (ceil($maxPrice / $range) > 25) {
                                $range *= 10;
                            }
                        }
                    }
                    else {
                        $range = Mage::app()->getStore()->getConfig('catalog/layered_navigation/price_range_step');
                    }
                }

            }
            $this->setData('price_range', $range);
        }
        return $this->getData('price_range');
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