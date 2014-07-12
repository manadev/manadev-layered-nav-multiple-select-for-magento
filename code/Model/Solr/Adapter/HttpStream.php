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
class Mana_Filters_Model_Solr_Adapter_HttpStream extends Enterprise_Search_Model_Adapter_HttpStream
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
                    if (isset($value['from']) || isset($value['to'])) {
                        if ($this->coreHelper()->startsWith($field, 'min-max:')) {
                            list($minField, $maxField) = explode(',', substr($field, strlen('min-max:')));
                            $fieldCondition = $this->_mRenderMinMaxRangeCondition($minField, $maxField, $value);
                        }
                        else {
                            $fieldCondition = $this->_mRenderRangeCondition($field, $value);
                        }
                    }
                    elseif (isset($value['or'])) {
                        $fieldCondition = '(' . implode(' OR ', $this->_mRenderCondition($field, $value['or'])) . ')';
                    }
                    elseif (isset($value['and'])) {
                        $fieldCondition = '(' . implode(' AND ', $this->_mRenderCondition($field, $value['and'])) . ')';
                    }
                    elseif (isset($value['reverse'])) {
                        $fieldCondition = '-(' . implode(' OR ', $this->_mRenderCondition($field, $value['reverse'])) . ')';
                    }
                    else {
                        $fieldCondition = '(' . implode(' OR ', $this->_mRenderCondition($field, $value)) . ')';
                    }
                }
                else {
                    $fieldCondition = $this->_mRenderEqCondition($field, $value);
                }

                $result[] = $fieldCondition;
            }
        }

        return $result;
    }

    protected function _mRenderCondition($field, $parts)
    {
        $fieldCondition = array();
        foreach ($parts as $part) {
            if (is_array($part) && (isset($part['from']) || isset($part['to']))) {
                if ($this->coreHelper()->startsWith($field, 'min-max:')) {
                    list($minField, $maxField) = explode(',', substr($field, strlen('min-max:')));
                    $fieldCondition[] = $this->_mRenderMinMaxRangeCondition($minField, $maxField, $part);
                } else {
                    $fieldCondition[] = $this->_mRenderRangeCondition($field, $part);
                }
            } else {
                $fieldCondition[] = $this->_mRenderEqCondition($field, $part);
            }
        }

        return $fieldCondition;
    }

    protected function _mRenderRangeCondition($field, $part)
    {
        $from = (isset($part['from']) && !empty($part['from']))
                ? $this->_prepareFilterQueryText($part['from'])
                : '*';
        $to = (isset($part['to']) && !empty($part['to']))
                ? $this->_prepareFilterQueryText($part['to'])
                : '*';
        return "$field:[$from TO $to]";
    }

    protected function _mRenderMinMaxRangeCondition($minField, $maxField, $part) {
        $from = $this->_prepareFilterQueryText($part['from']);
        $to = $this->_prepareFilterQueryText($part['to']);

        return "($minField:[$from TO *] OR $maxField:[$from TO *]) AND ($minField:[* TO $to] OR $minField:[* TO $to])";
    }

    protected function _mRenderRangeConditionEx($field, $part)
    {
        $from = (isset($part['from']) && !empty($part['from']))
                ? $this->_prepareFilterQueryText($part['from'])
                : '*';
        $to = (isset($part['to']) && !empty($part['to']))
                ? $this->_prepareFilterQueryText($part['to'])
                : '*';
        $o = !empty($part['lower_bound_exclusive']);
        $c = !empty($part['upper_bound_exclusive']);
        if ($from == '*') {
            if ($to == '*') {
                $middle = false;
                $o = $c = false;
            }
            else {
                $middle = $to - 1;
            }
        }
        else {
            if ($to == '*') {
                $middle = $from + 1;
            }
            else {
                $middle = round(($from - $to) / 2, 3);
            }
        }
        if ($middle === false || !$o && !$c) {
            return "$field:[$from TO $to]";
        }
        elseif ($c && $o) {
            return "$field:{{$from} TO $to}";
        }
        elseif ($c) {
            return "($field:[$from TO $middle] OR $field:{{$middle} TO $to})";
        }
        else {
            return "($field:{{$from} TO $middle} OR $field:[$middle TO $to])";
        }
    }

    protected function _mRenderEqCondition($field, $part)
    {
        $part = $this->_prepareFilterQueryText($part);
        return $this->_prepareFieldCondition($field, $part);
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
    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    function coreHelper() {
        return Mage::helper('mana_core');
    }

    #endregion
}