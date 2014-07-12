<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Resources/Single model DB operations */
/**
 * This resource model handles DB operations with a single model of type Mana_Filters_Model_Filter2_Value. All 
 * database specific code for Mana_Filters_Model_Filter2_Value should go here.
 * @author Mana Team
 */
class Mana_Filters_Resource_Filter2_Value extends Mana_Db_Resource_Object {
    #region bit indexes for default_mask field(s)
    const DM_NAME = 0;
    const DM_POSITION = 1;
    const DM_COLOR = 2;
    const DM_NORMAL_IMAGE = 3;
    const DM_SELECTED_IMAGE = 4;
    const DM_NORMAL_HOVERED_IMAGE = 5;
    const DM_SELECTED_HOVERED_IMAGE = 6;
    const DM_STATE_IMAGE = 7;

    const DM_CONTENT_IS_ACTIVE = 8;
    const DM_CONTENT_IS_INITIALIZED = 9;
    const DM_CONTENT_STOP_FURTHER_PROCESSING = 10;
    const DM_CONTENT_META_TITLE = 11;
    const DM_CONTENT_META_KEYWORDS = 12;
    const DM_CONTENT_META_DESCRIPTION = 13;
    const DM_CONTENT_META_ROBOTS = 14;
    const DM_CONTENT_TITLE = 15;
    const DM_CONTENT_SUBTITLE = 16;
    const DM_CONTENT_DESCRIPTION = 17;
    const DM_CONTENT_ADDITIONAL_DESCRIPTION = 18;
    const DM_CONTENT_LAYOUT_XML = 19;
    const DM_CONTENT_WIDGET_LAYOUT_XML = 20;
    const DM_CONTENT_PRIORITY = 21;
    const DM_CONTENT_COMMON_DIRECTIVES = 22;
    const DM_CONTENT_BACKGROUND_IMAGE = 23;

    #endregion

    public function loadByFilterPosition($object, $filterId, $position)
    {
        $read = $this->_getReadAdapter();

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable() . '.' . 'filter_id' . '=?', $filterId)
            ->where($this->getMainTable() . '.' . 'position' . '=?', $position);

        if ($read) {
            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_init(strtolower('Mana_Filters/Filter2_Value'), 'id');
        $this->_isPkAutoIncrement = false;
    }   
	protected function _getReplicationSources() {
		return array('eav/attribute', 'mana_filters/filter2');
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationUpdateSelects($target, $options) {
		/* @var $select Varien_Db_Select */ $select = $options['db']->select();
		$select
			->from(array('eav_attribute' => Mage::getSingleton('core/resource')->getTableName('eav/attribute')), null)
			->joinInner(array('eav_attribute_option' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option')), 
				'eav_attribute.attribute_id = eav_attribute_option.attribute_id', null)
			->joinInner(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				'target.option_id = eav_attribute_option.option_id', 
				array('target.id AS id', 'target.option_id AS option_id', 'target.filter_id AS filter_id'))
			->joinInner(array('parent' => Mage::getSingleton('core/resource')->getTableName('mana_filters/filter2')), 
				'target.filter_id = parent.id', null)
			->joinLeft(array('global_option_value' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value')),
				'global_option_value.option_id = eav_attribute_option.option_id AND global_option_value.store_id = 0', null)
			->distinct()
			->where('target.edit_status = 0')
			->columns(array(
				'target.default_mask0 AS default_mask0',
				'global_option_value.value_id AS value_id',
				'global_option_value.value AS name',
				'eav_attribute_option.sort_order AS position',
			));
			
		if ($options['trackKeys']) {
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('eav_attribute.attribute_id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets'][$this->getEntityName()]->getSavedKeys()) && count($keys)) {
				$select->where('target.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets']['mana_filters/filter2']->getSavedKeys()) && count($keys)) {
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
			->setOptionId($values['option_id'])
			->setFilterId($values['filter_id'])
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
		/* @var $select Varien_Db_Select */ $select = $options['db']->select();
		$select
			->from(array('eav_attribute' => Mage::getSingleton('core/resource')->getTableName('eav/attribute')), null)
			->joinInner(array('eav_attribute_option' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option')), 
				'eav_attribute.attribute_id = eav_attribute_option.attribute_id', 
				'eav_attribute_option.option_id AS option_id')
			->joinInner(array('parent' => Mage::getSingleton('core/resource')->getTableName('mana_filters/filter2')), 
				'eav_attribute.attribute_code = parent.code', 
				'parent.id AS filter_id')
			->joinLeft(array('global_option_value' => Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value')),
				'global_option_value.option_id = eav_attribute_option.option_id AND global_option_value.store_id = 0', null)
			->joinLeft(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				'target.option_id = eav_attribute_option.option_id', null)
			->distinct()
			->where('target.id IS NULL')
			->columns(array(
				'global_option_value.value_id AS value_id',
				'global_option_value.value AS name',
				'eav_attribute_option.sort_order AS position',
			));
			
		if ($options['trackKeys']) {
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('eav_attribute.attribute_id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets']['mana_filters/filter2']->getSavedKeys()) && count($keys)) {
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
			->setOptionId($values['option_id'])
			->setFilterId($values['filter_id'])
			->setValueId($values['value_id'])
			->setData('_m_prevent_replication', true);
			
		$object->setName($values['name']);
		$object->setPosition($values['position']);
	}
}