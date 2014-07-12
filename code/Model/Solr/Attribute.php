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
class Mana_Filters_Model_Solr_Attribute extends Mana_Filters_Model_Filter_Attribute
{
    public function isCountedOnMainCollection() {
        return !$this->isApplied();
    }

    public function processCounts($counts) {
        /* @var $collection Enterprise_Search_Model_Resource_Collection */
        $collection = $counts;

        $result = $collection->getFacetedData($this->getFilterField(), array('fields' => array('id')));
        $engine = Mage::getResourceSingleton('enterprise_search/engine');
        if (method_exists($engine, 'getSearchEngineFieldName')) {
            return $result;
        }
        else {
            $attribute = $this->getAttributeModel();
            $options = $attribute->getFrontend()->getSelectOptions();
            $idResult = array();
            foreach ($options as $option) {
                if (!$option || is_array($option['value'])) {
                    continue;
                }
                if (isset($result[$option['label']])) {
                    $idResult[$option['value']] = $result[$option['label']];
                }
            }
            return $idResult;
        }
    }

    /**
     * Get facet field name
     *
     * @return string
     */
    public function getFilterField() {
        $engine = Mage::getResourceSingleton('enterprise_search/engine');
        if (method_exists($engine, 'getSearchEngineFieldName')) {
            return $engine->getSearchEngineFieldName($this->getAttributeModel(), 'nav');
        }
        else {
            return Mage::helper('enterprise_search')->getAttributeSolrFieldName($this->getAttributeModel());
        }
    }

    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function countOnCollection($collection)
    {
        $collection->setFacetCondition($this->getFilterField());

        return $collection;
    }

    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     */
    public function applyToCollection($collection)
    {
        $values = $this->getMSelectedValues();
        $engine = Mage::getResourceSingleton('enterprise_search/engine');
        if (!method_exists($engine, 'getSearchEngineFieldName')) {
            $labels = array();
            foreach ($values as $value) {
                $labels[] = $this->getAttributeModel()->getFrontend()->getOption($value);
            }
            $values = $labels;
        }
        $collection->addFqFilter(array($this->getFilterField() => array('or' => $values)));
    }

}