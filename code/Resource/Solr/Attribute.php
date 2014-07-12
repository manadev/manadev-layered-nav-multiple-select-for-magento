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
class Mana_Filters_Resource_Solr_Attribute extends Mana_Filters_Resource_Filter_Attribute
{
    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     * @param Mana_Filters_Model_Solr_Attribute $model
     * @return Mana_Filters_Resource_Solr_Attribute
     */
    public function countOnCollection($collection, $model)
    {
        $collection->setFacetCondition($model->getFilterField());

        return $collection;
    }

    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     * @param Mana_Filters_Model_Filter_Attribute $model
     * @param array $value
     * @return Mana_Filters_Resource_Solr_Attribute
     */
    public function applyToCollection($collection, $model, $value)
    {
        $collection->addFqFilter(array($model->getFilterField() => array('or' => $value)));
    }
}