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
class Mana_Filters_Model_Solr_Price extends Mana_Filters_Model_Filter_Price
{
    public function isCountedOnMainCollection() {
        return !$this->isApplied();
    }

    /**
     * Applies counting query to the current collection. The result should be suitable to processCounts() method.
     * Typically, this method should return final result - option id/count pairs for option lists or
     * min/max pair for slider. However, in some cases (like not applied Solr facets) this method returns collection
     * object and later processCounts() extracts actual counts from this collections.
     *
     * @param Enterprise_Search_Model_Resource_Collection $collection
     * @return mixed
     */
    public function countOnCollection($collection)
    {
        if (Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION) == 'improved') {
            return $this->_addCalculatedFacetConditionToCollection($collection);
        }

        $this->_facets = array();
        $range = $this->getPriceRange();
        $maxPrice = $this->getMaxPriceInt();
        if ($maxPrice > 0) {
            $priceFacets = array();
            $facetCount  = ceil($maxPrice / $range);

            for ($i = 0; $i < $facetCount; $i++) {
                $separator = array($i * $range, ($i + 1) * $range);
                $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
                $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
                $priceFacets[] = $facetedRange;
            }

            $collection->setFacetCondition($this->_getFilterField(), $priceFacets);
        }

        return $collection;
    }
    public function processCounts($counts) {
        /* @var $collection Enterprise_Search_Model_Resource_Collection */
        $collection = $counts;

        $fieldName = $this->_getFilterField();
        $facets = $collection->getFacetedData($fieldName);
        $result = array();
        if (!empty($facets)) {
            foreach ($facets as $key => $value) {
                preg_match('/TO ([\d\.]+)\]$/', $key, $rangeKey);
                $rangeKey = $rangeKey[1] / $this->getPriceRange();
                $rangeKey = round($rangeKey);
                /** @noinspection PhpIllegalArrayKeyTypeInspection */
                $result[$rangeKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Applies filter values provided in URL to a given product collection
     *
     * @param Enterprise_Search_Model_Resource_Collection $collection
     * @return void
     */
    public function applyToCollection($collection)
    {
        $field             = $this->_getFilterField();
        $fq = array();
        foreach ($this->getMSelectedValues() as $selection) {
            if (strpos($selection, ',') !== false) {
                list($index, $range) = explode(',', $selection);
                $range = $this->_getResource()->getPriceRange($index, $range);
                $to = $range['to'];
                if ($to < $this->getMaxPriceInt() && !$this->isUpperBoundInclusive()) {
                    $to -= 0.001;
                }

                $fq[] = array(
                    'from' => $range['from'],
                    'to'   => $to,
                );
            }
        }

        $collection->addFqFilter(array($field => array('or' => $fq)));
    }

    #region Ported code
    const CACHE_TAG = 'MAXPRICE';

    /**
     * Whether current price interval is divisible
     *
     * @var bool
     */
    protected $_divisible = true;

    /**
     * Ranges faceted data
     *
     * @var array
     */
    protected $_facets = array();

    /**
     * Get facet field name based on current website and customer group
     *
     * @return string
     */
    protected function _getFilterField()
    {
        $engine = Mage::getResourceSingleton('enterprise_search/engine');
        if (method_exists($engine, 'getSearchEngineFieldName')) {
            $priceField = $engine->getSearchEngineFieldName('price');
        }
        else {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $priceField = 'price_' . $customerGroupId . '_' . $websiteId;
        }

        return $priceField;
    }

    /**
     * Get data with price separators
     *
     * @param $collection
     * @return array
     */
    protected function _getSeparatorsForCollection($collection)
    {
        $searchParams = $collection->getExtendedSearchParams();
        $intervalParams = $this->getInterval();
        $intervalParams = $intervalParams ? ($intervalParams[0] . '-' . $intervalParams[1]) : '';
        $uniquePart = strtoupper(md5(serialize(
            $searchParams . '_' . $this->getCurrencyRate() . '_' . $intervalParams . '_' . $this->isApplied()
        )));
        $cacheKey = 'PRICE_SEPARATORS_' . $this->getLayer()->getStateKey() . '_' . $uniquePart;

        $cachedData = Mage::app()->loadCache($cacheKey);
        if (!$cachedData) {
            /** @var $algorithmModel Mage_Catalog_Model_Layer_Filter_Price_Algorithm */
            $algorithmModel = Mage::getSingleton('catalog/layer_filter_price_algorithm');
            $statistics = $collection->getStats($this->_getFilterField());
            $statistics = $statistics[$this->_getFilterField()];

            $appliedInterval = $this->getInterval();
            if (
                $appliedInterval
                && ($statistics['count'] <= $this->getIntervalDivisionLimit()
                || $appliedInterval[0] == $appliedInterval[1]
                || $appliedInterval[1] === '0')
            ) {
                $algorithmModel->setPricesModel($this)->setStatistics(0, 0, 0, 0);
                $this->_divisible = false;
            } else {
                if ($appliedInterval) {
                    $algorithmModel->setLimits($appliedInterval[0], $appliedInterval[1]);
                }
                $algorithmModel->setPricesModel($this)->setStatistics(
                    round($statistics['min'] * $this->getCurrencyRate(), 2),
                    round($statistics['max'] * $this->getCurrencyRate(), 2),
                        $statistics['stddev'] * $this->getCurrencyRate(),
                    $statistics['count']
                );
            }

            $cachedData = array();
            foreach ($algorithmModel->calculateSeparators() as $separator) {
                $cachedData[] = $separator['from'] . '-' . $separator['to'];
            }
            $cachedData = implode(',', $cachedData);

            $tags = $this->getLayer()->getStateTags();
            $tags[] = self::CACHE_TAG;
            Mage::app()->saveCache($cachedData, $cacheKey, $tags);
        }

        if (!$cachedData) {
            return array();
        }

        $cachedData = explode(',', $cachedData);
        foreach ($cachedData as $k => $v) {
            $cachedData[$k] = explode('-', $v);
        }

        return $cachedData;
    }

    /**
     * Add params to faceted search generated by algorithm
     *
     * @param $collection
     * @return Enterprise_Search_Model_Catalog_Layer_Filter_Price
     */
    protected function _addCalculatedFacetConditionToCollection($collection)
    {
        $priceFacets = array();
        $this->_facets = array();
        foreach ($this->_getSeparatorsForCollection($collection) as $separator) {
            $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
            $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
            $priceFacets[] = $facetedRange;
        }
        $collection->setFacetCondition($this->_getFilterField(), $priceFacets);

        return $collection;
    }

    /**
     * Prepare price range to be added to facet conditions
     *
     * @param string|float $from
     * @param string|float $to
     * @return array
     */
    protected function _prepareFacetRange($from, $to)
    {
        if (empty($from)) {
            $from = '';
        }

        if ($to === '') {
            $to = '';
        } else {
            if ($to === $from || ($to === 0 && $from === '')) {
                $to = $this->_prepareFacetedValue($to, false);
            } else {
                $to = $this->_prepareFacetedValue($to);
            }
        }

        if ($from !== '') {
            $from = $this->_prepareFacetedValue($from, false);
        }

        return array('from' => $from, 'to' => $to);
    }

    /**
     * Prepare faceted value
     *
     * @param float $value
     * @param bool $decrease
     * @return float
     */
    protected function _prepareFacetedValue($value, $decrease = true) {
        if ($this->isUpperBoundInclusive()) {
            $decrease = false;
        }
        // rounding issue
        if ($this->getCurrencyRate() > 1) {
            if ($decrease) {
                $value -= 0.001;
            }
            $value /= $this->getCurrencyRate();
        } else {
            $value /= $this->getCurrencyRate();
            if ($decrease) {
                $value -= 0.001;
            }
        }
        return round($value, 3);
    }
    public function getRangeOnCollection($collection)
    {
        $min = 0;
        $stats = $collection->getStats($this->_getFilterField());

        $max = $stats[$this->_getFilterField()]['max'];
        if (!is_numeric($max)) {
            return parent::getRangeOnCollection($collection);
        } else {
            $max = $this->_ceil($max * $this->getCurrencyRate());
        }

        return compact('min', 'max');
    }

    public function isFilterAppliedWhenCounting($modelToBeApplied) {
        return false;
    }

    #endregion
}