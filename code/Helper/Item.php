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
class Mana_Filters_Helper_Item extends Mana_Core_Helper_Layer {
    protected $_allItems = array();
    protected $_attributeItems = array();
    protected $_resources;

    public function isEnabled() {
        return (bool)(string)Mage::getConfig()->getNode('mana_filters/item_repository/is_active');
    }

    /**
     * @return Mana_Filters_Resource_ItemAdditionalInfo[]
     * @throws Exception
     */
    public function getResources() {
        if (!$this->_resources) {
            $this->_resources = array();
            foreach ($this->coreHelper()->getSortedXmlChildren(Mage::getConfig()->getNode('mana_filters/item_repository'),
                'resources') as $key => $xml)
            {
                $this->_resources[$key] = Mage::getResourceSingleton((string)$xml->resource);
                if (!($this->_resources[$key] instanceof Mana_Filters_Resource_ItemAdditionalInfo)) {
                    throw new Exception(sprintf('%1 must be instance of %2', get_class($this->_resources[$key]),
                        'Mana_Filters_Resource_ItemAdditionalInfo'));

                }
            }
        }
        return $this->_resources;
    }

    /**
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @param Mana_Filters_Model_Item[] $items
     */
    public function registerItems($filter, $items) {
        if ($this->isEnabled()) {
            $this->_attributeItems[$filter->getAttributeModel()->getId()] = $items;
            $this->_allItems += $items;
        }
    }

    /**
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @return array|null
     */
    public function selectItems($filter) {
        if ($this->isEnabled()) {
            $select = $this->getResource()->selectItems($filter);
            foreach ($this->getResources() as $resource) {
                $resource->selectItems($select, $filter);
            }
            return $this->getResource()->fetch($select);
        }
        else {
            return null;
        }
    }

    /**
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return array|null
     */
    public function countItems($filter, $collection) {
        if ($this->isEnabled()) {
            $select = $this->getResource()->countItems($filter, $collection);
            foreach ($this->getResources() as $resource) {
                $resource->countItems($select, $filter, $collection);
            }
            return $this->getResource()->fetch($select);
        }
        else {
            return null;
        }
    }

    /**
     * @param int $attributeId
     * @return array|bool
     */
    public function getAttributeItems($attributeId) {
        return isset($this->_attributeItems[$attributeId]) ? $this->_attributeItems[$attributeId] : false;
    }

    /**
     * @param int $optionId
     * @return Mana_Filters_Model_Item|bool
     */
    public function get($optionId) {
        return isset($this->_allItems[$optionId]) ? $this->_allItems[$optionId] : false;
    }

    #region Dependencies

    /**
     * @return Mana_Filters_Resource_Item
     */
    public function getResource() {
        return Mage::getResourceSingleton('mana_filters/item');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    #endregion
}