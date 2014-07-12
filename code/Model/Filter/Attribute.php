<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Model type for holding information in memory about possible or applied filter which is based on an attribute
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_attribute in Mana_Filters_Block_Filter_Attribute constructor.
 *
 * @method Mana_Filters_Model_Filter2_Store getFilterOptions()
 */
class Mana_Filters_Model_Filter_Attribute
    extends Mage_Catalog_Model_Layer_Filter_Attribute
    implements Mana_Filters_Interface_Filter
{
    #region Attribute specific logic

    public function init() {
    }

    /**
     * Adds all selected items of this filters to the layered navigation state object
     *
     * @return void
     */
    public function addToState()
    {
        foreach ($this->getMSelectedValues() as $optionId) {
            $label = $this->getAttributeModel()->getFrontend()->getOption($optionId);
            $this->getLayer()->getState()->addFilter(
                $this->_createItemEx(
                    array(
                        'label' => $label,
                        'value' => $optionId,
                        'm_selected' => true,
                        'm_show_selected' => $this->getFilterOptions()->getIsReverse(),
                    )
                )
            );
        }
    }

    /**
     * Depending on current filter values and on attribute settings, returns available filter options from database
     * and additionally whether individual options are selected or not.
     * @return array
     * @see Mage_Catalog_Model_Layer_Filter_Attribute::_getItemsData()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    protected function _getItemsData()
    {
        /* @var $query Mana_Filters_Model_Query */
        $query = $this->getQuery();

        /* @var $helper Mana_Filters_Helper_Data */
        $helper = Mage::helper('mana_filters');

        // MANA BEGIN: from url, retrieve ids of all options currently selected
        $selectedOptionIds = $this->getMSelectedValues();
        // MANA END

        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $key = $this->getLayer()->getStateKey() . '_' . $this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null && $this->itemHelper()->isEnabled() &&
            $this->_getIsFilterable() == self::OPTIONS_ONLY_WITH_RESULTS &&
            !$helper->useSolr())
        {
            $data = $query->getFilterCounts($this->getFilterOptions()->getCode());
        }
        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $query->getFilterCounts($this->getFilterOptions()->getCode());
            $data = array();

            foreach ($options as $option) {
                if (!$option || is_array($option['value'])) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    $isSelected = in_array($option['value'], $selectedOptionIds);
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($optionsCount[$option['value']]) || in_array($option['value'], $selectedOptionIds)) {
                            $data[] = array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                                'm_selected' => $isSelected,
                                'm_show_selected' => $this->getFilterOptions()->getIsReverse(
                                ) ? !$isSelected : $isSelected,
                            );
                        }
                    } else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                            'm_selected' => $isSelected,
                            'm_show_selected' => $this->getFilterOptions()->getIsReverse() ? !$isSelected : $isSelected,
                        );
                    }
                }
            }
        }


        $tags = array(
            Mage_Eav_Model_Entity_Attribute::CACHE_TAG . ':' . $attribute->getId()
        );

        $tags = $this->getLayer()->getStateTags($tags);

        $sortMethod = $this->getFilterOptions()->getSortMethod() ? $this->getFilterOptions()->getSortMethod() : 'byPosition';
        foreach (array_keys($data) as $position => $key) {
            $data[$key]['position'] = $position;
        }
        usort($data, array(Mage::getSingleton('mana_filters/sort'), $sortMethod));

        $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);

        return $data;
    }
    #endregion

    #region Logic common for all non-category filters
    /**
     * Applies filter values provided in URL to a given product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return void
     */
    protected function _applyToCollection($collection, $value = null)
    {
        $this->_getResource()->applyToCollection($collection, $this, is_null($value) ? $this->getMSelectedValues() : $value);
    }

    #endregion

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
        return $this->itemHelper()->isEnabled() && $this->_getIsFilterable() == self::OPTIONS_ONLY_WITH_RESULTS
            ? $this->itemHelper()->countItems($this, $collection)
            : $this->_getResource()->countOnCollection($collection, $this);
    }

    public function optimizedCountOnCollection($collection, $attributeIds) {
        return $this->_getResource()->optimizedCountOnCollection($collection, $this, $attributeIds);
    }

    public function getRangeOnCollection($collection)
    {
        return array();
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
        return $modelToBeApplied != $this || $this->getFilterOptions()->getData('operation');
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
            $items[$itemData['value']] = $this->_createItemEx($itemData);
        }
        $items = $ext->processFilterItems($this, $items);
        $this->itemHelper()->registerItems($this, $items);
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

    /**
     * Returns all values currently selected for this filter
     */
    public function getMSelectedValues()
    {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        $values = $core->sanitizeRequestNumberParam(
            $this->_requestVar,
            array(array('sep' => '_', 'as_string' => true))
        );

        return $values ? array_filter(explode('_', $values)) : array();
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

    /**
     * @return Mana_Filters_Helper_Item
     */
    public function itemHelper() {
        return Mage::helper('mana_filters/item');
    }
    #endregion
}
