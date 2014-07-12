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
class Mana_Filters_Model_Solr_Reverse_Attribute extends Mana_Filters_Model_Solr_Attribute
{
    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     */
    public function applyToCollection($collection)
    {
        $engine = Mage::getResourceSingleton('enterprise_search/engine');
        $collection->addFqFilter(array($this->getFilterField() => array('reverse' => $this->getMSelectedValues())));
    }

}