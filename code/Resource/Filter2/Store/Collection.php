<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Resources/DB operations with model collections */
/**
 * This resource model handles DB operations with a collection of models of type Mana_Filters_Model_Filter2_Store. All 
 * database specific code for operating collection of Mana_Filters_Model_Filter2_Store should go here.
 * @author Mana Team
 */
class Mana_Filters_Resource_Filter2_Store_Collection extends Mana_Filters_Resource_Filter2_Collection {
    protected $_eventPrefix = 'mana_filter_store_collection';
    protected $_eventObject = 'collection';

    /**
     * Invoked during resource collection model creation process, this method associates this 
     * resource collection model with model class and with resource model class
     */
    protected function _construct()
    {
        $this->_init(strtolower('Mana_Filters/Filter2_Store'));
    }
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Virtual_Result $result
	 * @param Varien_Db_Select $select
	 * @param array $columns
	 */
	protected function _addVirtualColumns($result, $select, $columns = null) {
		$globalEntityName = Mage::helper('mana_db')->getGlobalEntityName($this->getEntityName());
		if (!$columns || in_array('code', $columns)) {
			Mage::helper('mana_db')->joinLeft($select, 
				'global', Mage::getSingleton('core/resource')->getTableName($globalEntityName),
				'main_table.global_id = global.id');
			$select->columns("global.code AS code");
			$result->addColumn('code');
		}
		if (!$columns || in_array('type', $columns)) {
			Mage::helper('mana_db')->joinLeft($select, 
				'global', Mage::getSingleton('core/resource')->getTableName($globalEntityName),
				'main_table.global_id = global.id');
			$select->columns("global.type AS type");
			$result->addColumn('type');
		}

        if ($this->coreHelper()->isManadevDependentFilterInstalled()) {
            $this->getDependentFilterVirtualColumnsResource()->addToCollection($select, $result, $columns, $globalEntityName);
        }
	}
	public function addGlobalFields($fields) {
	    $select = $this->_select;
        $globalEntityName = Mage::helper('mana_db')->getGlobalEntityName($this->getEntityName());
        Mage::helper('mana_db')->joinLeft($select,
            'global', Mage::getSingleton('core/resource')->getTableName($globalEntityName),
            'main_table.global_id = global.id');
        $fields = array_merge(array('default_mask0'), $fields);
        foreach ($fields as $field) {
            $select->columns("global.$field AS global_$field");
        }
        return $this;
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return ManaPro_FilterDependent_Resource_VirtualColumns
     */
    public function getDependentFilterVirtualColumnsResource() {
        return Mage::getResourceSingleton('manapro_filterdependent/virtualColumns');
    }
    #endregion
}
