<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: Resources/Single model DB operations */
/**
 * This resource model handles DB operations with a single model of type Mana_Filters_Model_Filter2_Value_Store. All 
 * database specific code for Mana_Filters_Model_Filter2_Value_Store should go here.
 * @author Mana Team
 */
class Mana_Filters_Resource_Filter2_Value_Store extends Mana_Filters_Resource_Filter2_Value {
    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_init(strtolower('Mana_Filters/Filter2_Value_Store'), 'id');
        $this->_isPkAutoIncrement = false;
    }   
	protected function _getReplicationSources() {
		return array('mana_filters/filter2_value', 'core/store', 'eav/attribute', 'mana_filters/filter2_store');
	}
    /**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationUpdateSelects($target, $options) {
		$globalEntityName = Mage::helper('mana_db')->getGlobalEntityName($this->getEntityName());
		/* @var $select Varien_Db_Select */ $select = $options['db']->select();
		$select
			->from(array('global' => Mage::getSingleton('core/resource')->getTableName($globalEntityName)), null)
			->joinInner(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				'target.global_id = global.id AND global.edit_status = 0',
				array('target.id AS id', 'target.global_id AS global_id', 'target.store_id AS store_id', 
					'target.filter_id AS filter_id'))
			->joinInner(array('eav_attribute_option' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option')), 
				'global.option_id = eav_attribute_option.option_id', null)
			->joinInner(array('parent' => Mage::getSingleton('core/resource')->getTableName('mana_filters/filter2_store')), 
				'target.filter_id = parent.id', null)
			->joinLeft(array('global_option_value' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value')),
				'global_option_value.option_id = eav_attribute_option.option_id AND global_option_value.store_id = 0', null)
			->joinLeft(array('store_option_value' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value')),
				'store_option_value.option_id = eav_attribute_option.option_id AND store_option_value.store_id = target.store_id', null)
			->distinct()
			->where('target.edit_status = 0')
			->columns(array(
				'target.default_mask0 AS default_mask0',
				'global.option_id AS option_id',
				'COALESCE(store_option_value.value_id, global_option_value.value_id) AS value_id',
				"COALESCE(store_option_value.value, global_option_value.value, '') AS name",
				'global.position AS position',
			));
			
		if ($options['trackKeys']) {
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('eav_attribute_option.attribute_id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets'][$globalEntityName]->getSavedKeys()) && count($keys)) {
				$select->where('global.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets'][$this->getEntityName()]->getSavedKeys()) && count($keys)) {
				$select->where('target.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets']['mana_filters/filter2_store']->getSavedKeys()) && count($keys)) {
				$select->where('parent.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
		}
		$target->setSelect('main', $select);
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Object $object
	 * @param array $values
	 * @param array $options
	 */
	protected function _processReplicationUpdate($object, $values, $options) {
		$object
			->setId($values['id'])
			->setGlobalId($values['global_id'])
			->setStoreId($values['store_id'])
			->setFilterId($values['filter_id'])
			->setOptionId($values['option_id'])
			->setValueId($values['value_id'])
			->setData('_m_prevent_replication', true);
			
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2_Value::DM_NAME)) {
			$object->setName($values['name']);
		}
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2_Value::DM_POSITION)) {
			$object->setPosition($values['position']);
		}
	}
	
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationInsertSelects($target, $options) {
		$globalEntityName = Mage::helper('mana_db')->getGlobalEntityName($this->getEntityName());
		/* @var $select Varien_Db_Select */ $select = $options['db']->select();
		$select
			->from(array('global' => Mage::getSingleton('core/resource')->getTableName($globalEntityName)), 'global.id AS global_id')
			->from(array('core_store' => Mage::getSingleton('core/resource')->getTableName('core_store')), 'core_store.store_id AS store_id')
			->joinInner(array('eav_attribute_option' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option')), 
				'global.option_id = eav_attribute_option.option_id', null)
			->joinInner(array('eav_attribute' => Mage::getSingleton('core/resource')->getTableName('eav/attribute')), 
				'eav_attribute.attribute_id = eav_attribute_option.attribute_id', null)
			->joinInner(array('parent_global' => Mage::getSingleton('core/resource')->getTableName('mana_filters/filter2')), 
				'eav_attribute.attribute_code = parent_global.code', null)
			->joinInner(array('parent' => Mage::getSingleton('core/resource')->getTableName('mana_filters/filter2_store')), 
				'parent_global.id = parent.global_id AND core_store.store_id = parent.store_id', 
				'parent.id AS filter_id')
			->joinLeft(array('global_option_value' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value')),
				'global_option_value.option_id = eav_attribute_option.option_id AND global_option_value.store_id = 0', null)
			->joinLeft(array('store_option_value' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value')),
				'store_option_value.option_id = eav_attribute_option.option_id AND store_option_value.store_id = core_store.store_id', null)
			->joinLeft(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				'target.global_id = global.id AND target.store_id = core_store.store_id', null)
			->distinct()
			->where('core_store.store_id <> 0')
			->where('global.edit_status = 0')
			->where('target.id IS NULL')
			->columns(array(
				'global.option_id AS option_id',
				'COALESCE(store_option_value.value_id, global_option_value.value_id) AS value_id',
				"COALESCE(store_option_value.value, global_option_value.value, '') AS name",
				'global.position AS position',
			));
			
		if ($options['trackKeys']) {
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('eav_attribute_option.attribute_id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets'][$globalEntityName]->getSavedKeys()) && count($keys)) {
				$select->where('global.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets']['mana_filters/filter2_store']->getSavedKeys()) && count($keys)) {
				$select->where('parent.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
		}
		$target->setSelect('main', $select);
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Object $object
	 * @param array $values
	 * @param array $options
	 */
	protected function _processReplicationInsert($object, $values, $options) {
		$object
			->setGlobalId($values['global_id'])
			->setStoreId($values['store_id'])
			->setFilterId($values['filter_id'])
			->setOptionId($values['option_id'])
			->setValueId($values['value_id'])
			->setData('_m_prevent_replication', true);
			
		$object->setName($values['name']);
		$object->setPosition($values['position']);
	}

    public function loadByFilterPosition($object, $filterId, $position) {
        throw new Exception('Not implemented');
    }
}