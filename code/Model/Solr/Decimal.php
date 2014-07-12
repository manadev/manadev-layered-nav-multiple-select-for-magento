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
class Mana_Filters_Model_Solr_Decimal extends Mana_Filters_Model_Filter_Decimal
{
    public function isCountedOnMainCollection() {
        return !$this->isApplied();
    }

    public function processCounts($counts) {
        /* @var $collection Enterprise_Search_Model_Resource_Collection */
        $collection = $counts;

        $attributeCode = $this->getAttributeModel()->getAttributeCode();
        $fieldName = 'attr_decimal_' . $attributeCode;
        $facets = $collection->getFacetedData($fieldName);
        $result = array();
        if (!empty($facets)) {
            foreach ($facets as $key => $value) {
                preg_match('/TO ([\d\.]+)\]$/', $key, $rangeKey);
                $rangeKey = $rangeKey[1] / $this->getRange();
                $rangeKey = round($rangeKey);
                /** @noinspection PhpIllegalArrayKeyTypeInspection */
                $result[$rangeKey] = $value;
            }
        }
        return $result;
    }

    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     * @return mixed
     */
    public function countOnCollection($collection)
    {
        $range = $this->getRange();
        $maxValue = $this->getMaxValue();
        if ($maxValue > 0) {
            $facets = array();
            $facetCount = ceil($maxValue / $range);
            for ($i = 0; $i < $facetCount; $i++) {
                $facets[] = array(
                    'from' => $i * $range,
                    'to' => ($i + 1) * $range - ($this->isUpperBoundInclusive() ? 0 : 0.001),
                );
            }

            $attributeCode = $this->getAttributeModel()->getAttributeCode();
            $field = 'attr_decimal_' . $attributeCode;

            $collection->setFacetCondition($field, $facets);
        }

        return $collection;
    }

    /**
     * @param Enterprise_Search_Model_Resource_Collection $collection
     */
    public function applyToCollection($collection)
    {
        $attributeCode     = $this->getAttributeModel()->getAttributeCode();
        $field             = 'attr_decimal_'. $attributeCode;

        $fq = array();
        foreach ($this->getMSelectedValues() as $selection) {
            if (strpos($selection, ',') !== false) {
                list($index, $range) = explode(',', $selection);
                $range = $this->_getResource()->getRange($index, $range);
                $fq[] = array(
                    'from' => $range['from'],
                    'to'   => $range['to'] - ($this->isUpperBoundInclusive() ? 0 : 0.001),
                );
            }
        }

        $collection->addFqFilter(array($field => array('or' => $fq)));
    }

    public function isFilterAppliedWhenCounting($modelToBeApplied) {
        return false;
    }
}