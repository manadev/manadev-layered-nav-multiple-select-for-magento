<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Virtual collection of filters combining info from multiple places in DB and XML configuration
 * @author Mana Team
 *
 */
class Mana_Filters_Resource_Filter_Collection_Derived extends Mana_Core_Resource_Eav_Collection_Derived {
    protected function _construct()
    {
        $this->_init(strtolower('Mana_Filters/Filter'));
    }
    protected function _addMissingOriginalItems() {
    	/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
    	
    	// load system filters
    	if ($core->arrayFind($this->_items, 'code', 'category') === false) {
    		$this->addItem($this->getNewEmptyItem()
    			->setCode('category')
    			->setStoreId($this->getStoreId())
    			->loadDefaults());
    	}
    	
    	// get filterable attributes
        $attributes = Mage::getSingleton('mana_filters/filter_default')->getFilterableAttributes($this->getStoreId());
        
    	// load attribute filters
        foreach ($attributes as $attribute) {
	    	if ($core->arrayFind($this->_items, 'code', $attribute->getAttributeCode()) === false) {
	    		$this->addItem($this->getNewEmptyItem()
	    			->setCode($attribute->getAttributeCode())
	    			->setAttribute($attribute)
	    			->setStoreId($this->getStoreId())
	    			->loadDefaults());
	    	}
	    }
    	
    	return $this;
    }
    protected function _renderFilters() {
    	parent::_renderFilters();
    	if ($this->_codeFilter) {
			$items = array();
			foreach ($this->_items as $key => $item) {
				if (in_array($item->getCode(), $this->_codeFilter)) {
					$items[$key] = $item;
				}
			}
			$this->_items = $items;
    	}
    	return $this;
    }

    protected $_codeFilter;
    public function addCodeFilter($codes) {
    	$this->_codeFilter = $codes;
    	$this->getSelect()->where('e.code in (?)', $codes);
    	return $this;
    }
}