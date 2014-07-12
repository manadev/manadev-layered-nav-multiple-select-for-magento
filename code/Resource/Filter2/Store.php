<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Resources/Single model DB operations */
/**
 * This resource model handles DB operations with a single model of type Mana_Filters_Model_Filter2_Store. All 
 * database specific code for Mana_Filters_Model_Filter2_Store should go here.
 * @author Mana Team
 */
class Mana_Filters_Resource_Filter2_Store extends Mana_Filters_Resource_Filter2 {
    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_init(strtolower('Mana_Filters/Filter2_Store'), 'id');
        $this->_isPkAutoIncrement = false;
    }   
	protected function _getReplicationSources() {
		return array('mana_filters/filter2', 'core/store', 'eav/attribute');
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
				'target.global_id = global.id', 
				array('target.id AS id', 'target.global_id AS global_id', 'target.store_id AS store_id'))
			->joinLeft(array('eav_attribute' => Mage::getSingleton('core/resource')->getTableName('eav/attribute')), 
				'global.code = eav_attribute.attribute_code', null)
			->joinLeft(array('eav_attribute_label' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_label')), 
				'eav_attribute.attribute_id = eav_attribute_label.attribute_id AND target.store_id = eav_attribute_label.store_id', 
				null)
			->distinct()
			->columns(array(
				'target.default_mask0 AS default_mask0',
				'target.default_mask1 AS default_mask1',
				'global.is_enabled AS is_enabled',
				'global.display AS display',
				'COALESCE(eav_attribute_label.value, global.name) AS name',
				'global.is_enabled_in_search AS is_enabled_in_search',
				'global.position AS position',
                'global.sort_method AS sort_method',
                'global.operation AS operation',
                'global.is_reverse AS is_reverse',
                'global.disable_no_result_options AS disable_no_result_options',
            ));
		if ($options['trackKeys']) {
			if (($keys = $options['targets'][$globalEntityName]->getSavedKeys()) && count($keys)) {
				$select->where('global.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets'][$this->getEntityName()]->getSavedKeys()) && count($keys)) {
				$select->where('target.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('eav_attribute.attribute_id IN (?)', $keys);
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
			->setData('_m_prevent_replication', true);
			
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IS_ENABLED)) {
			$object->setIsEnabled($values['is_enabled']);
		}
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_DISPLAY)) {
			$object->setDisplay($values['display']);
		}
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_NAME)) {
			$object->setName($values['name']);
		}
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IS_ENABLED_IN_SEARCH)) {
			$object->setIsEnabledInSearch($values['is_enabled_in_search']);
		}
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_POSITION)) {
			$object->setPosition($values['position']);
		}
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_SORT_METHOD)) {
            $object->setSortMethod($values['sort_method']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_OPERATION)) {
            $object->setOperation($values['operation']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IS_REVERSE)) {
            $object->setIsReverse($values['is_reverse']);
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_DISABLE_NO_RESULT_OPTIONS)) {
            $object->setDisableNoResultOptions($values['disable_no_result_options']);
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
			->joinLeft(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				'target.global_id = global.id AND target.store_id = core_store.store_id', null)
			->joinLeft(array('eav_attribute' => Mage::getSingleton('core/resource')->getTableName('eav/attribute')), 
				'global.code = eav_attribute.attribute_code', null)
			->joinLeft(array('eav_attribute_label' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_label')), 
				'eav_attribute.attribute_id = eav_attribute_label.attribute_id AND target.store_id = eav_attribute_label.store_id', 
				null)
			->distinct()
			->where('core_store.store_id <> 0')
			->where('target.id IS NULL')
			->columns(array(
				'global.is_enabled AS is_enabled',
				'global.display AS display',
				'COALESCE(eav_attribute_label.value, global.name) AS name',
				'global.is_enabled_in_search AS is_enabled_in_search',
				'global.position AS position',
                'global.sort_method AS sort_method',
                'global.operation AS operation',
                'global.is_reverse AS is_reverse',
                'global.disable_no_result_options AS disable_no_result_options',
            ));
		if ($options['trackKeys']) {
			if (($keys = $options['targets'][$globalEntityName]->getSavedKeys()) && count($keys)) {
				$select->where('global.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('eav_attribute.attribute_id IN (?)', $keys);
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
			->setData('_m_prevent_replication', true);
			
		$object->setIsEnabled($values['is_enabled']);
		$object->setDisplay($values['display']);
		$object->setName($values['name']);
		$object->setIsEnabledInSearch($values['is_enabled_in_search']);
		$object->setPosition($values['position']);
        $object->setSortMethod($values['sort_method']);
        $object->setOperation($values['operation']);
        $object->setIsReverse($values['is_reverse']);
        $object->setDisableNoResultOptions($values['disable_no_result_options']);
    }
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationDeleteSelects($target, $options) {
	}
	/**
	 * Enter description here ...
	 * @param array $values
	 * @param array $options
	 */
	protected function _processReplicationDelete($values, $options) {
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
				$this->getMainTable().'.global_id = global.id');
			$select->columns("global.code AS code");
			$result->addColumn('code');
		}
		if (!$columns || in_array('type', $columns)) {
			Mage::helper('mana_db')->joinLeft($select, 
				'global', Mage::getSingleton('core/resource')->getTableName($globalEntityName),
				$this->getMainTable().'.global_id = global.id');
			$select->columns("global.type AS type");
			$result->addColumn('type');
		}

        if ($this->coreHelper()->isManadevDependentFilterInstalled()) {
            $this->getDependentFilterVirtualColumnsResource()->addToModel($this, $select, $result, $columns, $globalEntityName);
        }
	}
    /**
     * @param Mana_Filters_Model_Filter2_Store $filter
     * @return bool|int
     */
    public function getAttributeId($filter) {
        $db = $this->_getReadAdapter();

        return $db->fetchOne($db->select()
            ->from(array('a' => $this->getTable('eav/attribute')), 'attribute_id')
            ->joinInner(array('f' => $this->getTable('mana_filters/filter2')), "a.attribute_code = f.code", null)
            ->joinInner(
                array('t' => $this->getTable('eav/entity_type')),
                "`t`.`entity_type_id` = `a`.`entity_type_id` AND `t`.`entity_type_code` = 'catalog_product'",
                null
            )
            ->joinInner(
                array('ca' => $this->getTable('catalog/eav_attribute')),
                "`ca`.`attribute_id` = `a`.`attribute_id`",
                null
            )
            ->where('f.id = ?', $filter->getGlobalId()));
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