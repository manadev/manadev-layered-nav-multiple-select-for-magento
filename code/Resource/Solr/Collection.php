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
class Mana_Filters_Resource_Solr_Collection extends Enterprise_Search_Model_Resource_Collection {
    /**
     * Flag that defines if faceted data needs to be loaded
     *
     * @var bool
     */
    protected $_facetedDataIsLoaded = false;

    /**
     * Load faceted data if not loaded
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function loadFacetedData($additionalParams = array()) {
        if (empty($this->_facetedConditions)) {
            $this->_facetedData = array();

            return $this;
        }

        list($query, $params) = $this->_prepareBaseParams();
        $params['solr_params']['facet'] = 'on';
        $params['facet'] = $this->_facetedConditions;
        $params = array_merge($params, $additionalParams);

        $result = $this->_engine->getResultForRequest($query, $params);
        $this->_facetedData = isset($result['faceted_data']) ? $result['faceted_data'] : $result['facets'];
        $this->_facetedDataIsLoaded = true;

        return $this;
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     *
     * @return array
     */
    public function getFacetedData($field, $additionalParams = array()) {
        if (!$this->_facetedDataIsLoaded) {
            $this->loadFacetedData($additionalParams);
        }

        if (isset($this->_facetedData[$field])) {
            return $this->_facetedData[$field];
        }

        return array();
    }
}