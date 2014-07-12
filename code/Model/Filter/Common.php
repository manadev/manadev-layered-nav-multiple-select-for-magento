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
class Mana_Filters_Model_Filter_Common
{
    /**
     * @var Mana_Filters_Interface_Filter_Model
     */
    protected $_model;

    public function __construct(Mana_Filters_Interface_Filter_Model $model) {
        $this->_model = $model;
    }

    #region Mana_Filters_Interface_Filter_Query methods
    /**
     * Returns whether this filter is applied
     *
     * @return bool
     */
    public function isApplied()
    {
        $appliedValues = $this->_model->getMSelectedValues();
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
        $this->_model->getResource()->applyToCollection($collection, $this, $this->_model->getMSelectedValues());
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
        return $this->_model->getResource()->countOnCollection($collection, $this);
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
    public function isFilterAppliedWhenCounting($modelToBeApplied) {
        return $modelToBeApplied != $this->_model;
    }
    #endregion

    /**
     * Creates in-memory representation of a single option of a filter
     * @param array $data
     * @return Mana_Filters_Model_Item
     * This method is cloned from method _createItem() in parent class (method body was pasted from parent class
     * completely rewritten.
     * Standard method did not give us possibility to initialize non-standard fields.
     */
    public function createItemEx($data)
    {
        return Mage::getModel('mana_filters/item')
            ->setData($data)
            ->setFilter($this->_model);
    }

    /**
     * Initializes internal array of in-memory representations of options of a filter
     * @return Mana_Filters_Model_Filter_Attribute
     * @see Mage_Catalog_Model_Layer_Filter_Abstract::_initItems()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    public function initItems()
    {
        /* @var $ext Mana_Filters_Helper_Extended */
        $ext = Mage::helper(strtolower('Mana_Filters/Extended'));

        $data = $this->_model->getItemsData();
        $items=array();
        foreach ($data as $itemData) {
            $items[] = $this->_model->createItemEx($itemData);
        }
        $items = $ext->processFilterItems($this, $items);
        $this->_model->setItems($items);
        return $this;
    }

    /**
     * Returns all values currently selected for this filter
     */
    public function getMSelectedValues() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        $values = $core->sanitizeRequestNumberParam($this->_model->getRequestVar(),
            array(array('sep' => '_', 'as_string' => true)));
        return $values ? array_filter(explode('_', $values)) : array();
    }
    public function getMSelectedRangeValues() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        $values = $core->sanitizeRequestNumberParam($this->_model->getRequestVar(),
                array(array('sep' => '_', 'as_string' => true), array('sep' => ',', 'as_string' => true)));
        return $values ? explode('_', $values) : array();
    }

}