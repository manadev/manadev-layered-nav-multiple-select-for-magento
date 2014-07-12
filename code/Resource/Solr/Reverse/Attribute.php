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
class Mana_Filters_Resource_Solr_Reverse_Attribute extends Mana_Filters_Resource_Solr_Attribute
{
    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     * @param Mana_Filters_Model_Filter_Attribute $model
     * @param array $value
     * @return Mana_Filters_Resource_Solr_Attribute
     */
    public function applyToCollection($collection, $model, $value)
    {
        $collection->addFqFilter(array($model->getFilterField() => array('reverse' => $value)));
    }
}