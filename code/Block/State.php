<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Refined block which shows currently applied filter values
 * @author Mana Team
 */
class Mana_Filters_Block_State extends Mage_Catalog_Block_Layer_State {
	public function getClearUrl() {
        if ($this->getMode() == 'this') {
            $query = array('p' => null);
            foreach ($this->getActiveFilters() as $item) {
                $query[$item->getFilter()->getRequestVar()] = $item->getFilter()->getResetValue();
            }
            $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
            $params['_current'] = true;
            $params['_use_rewrite'] = true;
            $params['_m_escape'] = '';
            $params['_query'] = $query;
            return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
        }
        else {
            return Mage::helper('mana_filters')->getClearUrl();
        }
	}
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mana/filters/state.phtml');
    }
    public function getValueHtml($item) {
        $result = new Varien_Object();
        $block = $this;
        Mage::dispatchEvent('m_filter_value_html', compact('block', 'item', 'result'));
        return $result->getHtml() ? $result->getHtml() : '';
    }
    public function getActiveFilters() {
        $filters = parent::getActiveFilters();
        if ($this->getMode() == 'this') {
            $result = array();
            foreach ($filters as $item) {
                if ($this->_doesParentContainsFilter($item->getFilter())) {
                    $result[] = $item;
                }
            }
            return $result;
        }
        else {
            return $filters;
        }
    }
    protected function _doesParentContainsFilter($filter) {
        foreach (array_keys($this->getParentBlock()->getChild()) as $blockName) {
            if ($blockName == $filter->getFilterOptions()->getCode().'_filter') {
                return true;
            }
        }
        return false;
    }
}