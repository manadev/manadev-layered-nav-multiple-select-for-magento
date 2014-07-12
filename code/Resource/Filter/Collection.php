<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: Resources/DB operations with model collections */
/**
 * This resource model handles DB operations with a collection of models of type Mana_Filters_Model_Filter. All 
 * database specific code for operating collection of Mana_Filters_Model_Filter should go here.
 * @author Mana Team
 */
class Mana_Filters_Resource_Filter_Collection extends Mana_Core_Resource_Eav_Collection
{
    /**
     * Invoked during resource collection model creation process, this method associates this 
     * resource collection model with model class and with resource model class
     */
    protected function _construct()
    {
        $this->_init(strtolower('Mana_Filters/Filter'));
    }

    protected $_codeFilter;
    public function addCodeFilter($codes) {
    	$this->_codeFilter = $codes;
    	$this->getSelect()->where('e.code in (?)', $codes);
    	return $this;
    }
    protected function _isValidItem($item) {
    	if ($item->getCode() == 'category') {
    		return true;
    	}
    	else {
	    	/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
	        $attributes = Mage::getSingleton('mana_filters/filter_default')->getFilterableAttributes($this->getStoreId());
	    	if ($core->collectionFind($attributes, 'attribute_code', $item->getCode())) {
	    		return true;
	    	}
	    	else {
	    		/* @var $resource Mana_Filters_Resource_Filter */ $resource = $this->getResource();
	    		$resource->delete($item);
	    		return false;
	    	}
    	}
    }
}
