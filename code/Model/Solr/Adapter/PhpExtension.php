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
class Mana_Filters_Model_Solr_Adapter_PhpExtension extends Enterprise_Search_Model_Adapter_PhpExtension
{
    /**
     * Prepare fq filter conditions
     *
     * @param array $filters
     * @return array
     */
    protected function _prepareFilters($filters)
    {
        $result = array();

        if (is_array($filters) && !empty($filters)) {
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    if ($field == 'price' || isset($value['from']) || isset($value['to'])) {
                        $from = (isset($value['from']) && !empty($value['from']))
                                ? $this->_prepareFilterQueryText($value['from'])
                                : '*';
                        $to = (isset($value['to']) && !empty($value['to']))
                                ? $this->_prepareFilterQueryText($value['to'])
                                : '*';
                        $fieldCondition = "$field:[$from TO $to]";
                    } elseif (isset($value['or'])) {
                        $fieldCondition = array();
                        foreach ($value['or'] as $part) {
                            $part = $this->_prepareFilterQueryText($part);
                            $fieldCondition[] = $this->_prepareFieldCondition($field, $part);
                        }
                        $fieldCondition = '(' . implode(' OR ', $fieldCondition) . ')';
                    } elseif (isset($value['and'])) {
                        $fieldCondition = array();
                        foreach ($value['and'] as $part) {
                            $part = $this->_prepareFilterQueryText($part);
                            $fieldCondition[] = $this->_prepareFieldCondition($field, $part);
                        }
                        $fieldCondition = '(' . implode(' AND ', $fieldCondition) . ')';
                    } elseif (isset($value['reverse'])) {
                        $fieldCondition = array();
                        foreach ($value as $part) {
                            $part = $this->_prepareFilterQueryText($part);
                            $fieldCondition[] = $this->_prepareFieldCondition($field, $part);
                        }
                        $fieldCondition = '-(' . implode(' OR ', $fieldCondition) . ')';
                    } else {
                        $fieldCondition = array();
                        foreach ($value as $part) {
                            $part = $this->_prepareFilterQueryText($part);
                            $fieldCondition[] = $this->_prepareFieldCondition($field, $part);
                        }
                        $fieldCondition = '(' . implode(' OR ', $fieldCondition) . ')';
                    }
                } else {
                    $value = $this->_prepareFilterQueryText($value);
                    $fieldCondition = $this->_prepareFieldCondition($field, $value);
                }

                $result[] = $fieldCondition;
            }
        }

        return $result;
    }

    protected function _prepareFacetConditions($facetFields) {
        $result = parent::_prepareFacetConditions($facetFields);
        if (isset($result['facet']) && $result['facet'] == 'on' &&
            ($limit = Mage::getStoreConfig('mana_filters/general/solr_limit')) &&
            is_numeric($limit))
        {
            $result['facet.limit'] = (int)$limit;
        }
        return $result;
    }
}