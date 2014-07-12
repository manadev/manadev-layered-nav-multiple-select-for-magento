<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Default value provider which gets value from filterable attribute
 * @author Mana Team
 *
 */
class Mana_Filters_Model_Filter_Default {
	protected $_filterableAttributes;
	public function getFilterableAttributes($storeId) {
		if (!$this->_filterableAttributes) {
			$this->_filterableAttributes = array();
		}
		if (!isset($this->_filterableAttributes[$storeId])) {
	        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
	        	->addIsFilterableFilter()
	        	->setItemObjectClass('catalog/resource_eav_attribute')
            	->addStoreLabel($storeId);
	        $attributes->getSelect()->distinct(true);
	        $this->_filterableAttributes[$storeId] = $attributes; 
		} 
		return $this->_filterableAttributes[$storeId];
	}
	public function getDefaultValue($model, $attributeCode, $source) {
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		if (!($attribute = $core->collectionFind($this->getFilterableAttributes($model->getStoreId()), 'attribute_code', $model->getCode()))) {
			/* @var $attribute Mage_Catalog_Model_Entity_Attribute */ $attribute = Mage::getModel('catalog/entity_attribute')
				->loadByCode(Mage_Catalog_Model_Product::ENTITY, $model->getCode());
			if ($attribute->isObjectNew()) {
				switch ($model->getCode()) {
					case 'category': $attribute->setIsFilterable(1)->setIsFilterableInSearch(1)->setPosition(-1)->setStoreLabel(Mage::helper('mana_filters')->__('Category')); break;
					default: throw new Exception(Mage::helper('mana_filters')->__('Attribute %s not found.', $model->getCode()));
				}
			}
		}
		switch ($attributeCode) {
			case 'name': return $attribute->getStoreLabel();
			case 'is_enabled': return $attribute->getIsFilterable() ? 1 : 0;
			case 'is_enabled_in_search': return $attribute->getIsFilterableInSearch() ? 1 : 0;
			case 'position': return $attribute->getPosition();
			default: throw new Exception('Not implemented');
		}
	}
	public function getUseDefaultLabel() {
		return Mage::helper('mana_filters')->__('Use Product Attribute');
	}
}