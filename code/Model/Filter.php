<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Models/DB-backed model */
/**
 * In-memory model for individual filter settings 
 * @author Mana Team
 */
class Mana_Filters_Model_Filter extends Mana_Core_Model_Eav {
	const ENTITY = 'm_filter';
	
    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
	protected function _construct() {
		$this->_init(strtolower('Mana_Filters/Filter'));
		$this->_eventPrefix = self::ENTITY;
	}
	public function getType() {
		if ($this->getCode() == 'category') {
			return 'category'; 
		}
		elseif ($this->getCode() == 'price') {
			return 'price'; 
		}
		else {
			if ($this->getResource()->getBackendType($this->getCode()) == 'decimal') {
				return 'decimal';
			}
			else {
				return 'attribute'; 
			}
		}
	}
	public function getDisplayOptions() {
		return Mage::getConfig()->getNode('mana_filters/display/'.$this->getType().'/'.$this->getDisplay());	
	}
	public function getAttribute() {
		if ($this->getCode() == 'category') {
			return null;
		}
		if (!$this->hasData('attribute')) {
			/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
			$collection = Mage::getSingleton('mana_filters/filter_default')->getFilterableAttributes($this->getStoreId());
			$attribute = $core->collectionFind($collection, 'attribute_code', $this->getCode());
			$this->setAttribute($attribute);
		}
		return $this->getData('attribute'); 
	}
}
