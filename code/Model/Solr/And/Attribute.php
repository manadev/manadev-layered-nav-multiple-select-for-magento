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
class Mana_Filters_Model_Solr_And_Attribute extends Mana_Filters_Model_Solr_Attribute
{
    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     */
    public function applyToCollection($collection)
    {
        $engine = Mage::getResourceSingleton('enterprise_search/engine');
        $facetField = $engine->getSearchEngineFieldName($this->getAttributeModel(), 'nav');
        $collection->addFqFilter(array($facetField => array('and' => $this->getMSelectedValues())));
    }

}