<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Resources/Single model DB operations */
/**
 * This resource model handles DB operations with a single model of type Mana_Filters_Model_Filter2. All 
 * database specific code for Mana_Filters_Model_Filter2 should go here.
 * @author Mana Team
 */
class Mana_Filters_Resource_Filter2 extends Mana_Db_Resource_Object {
    #region bit indexes for default_mask field(s)
    const DM_IS_ENABLED = 0;
    const DM_DISPLAY = 1;
    const DM_NAME = 2;
    const DM_SHOW_MORE_ITEM_COUNT = 3;
    const DM_IS_ENABLED_IN_SEARCH = 4;
    const DM_POSITION = 5;
    const DM_IMAGE_WIDTH = 6;
    const DM_IMAGE_HEIGHT = 7;

    const DM_IMAGE_BORDER_RADIUS = 8;
    const DM_SLIDER_NUMBER_FORMAT = 9;
    const DM_SLIDER_MANUAL_ENTRY = 10;
    const DM_SLIDER_NUMBER_FORMAT2 = 11;
    const DM_SLIDER_THRESHOLD = 12;
    const DM_SLIDER_USE_EXISTING_VALUES = 13;
    const DM_IMAGE_NORMAL = 14;
    const DM_IMAGE_SELECTED = 15;

    const DM_IMAGE_NORMAL_HOVERED = 16;
    const DM_IMAGE_SELECTED_HOVERED = 17;
    const DM_STATE_WIDTH = 18;
    const DM_STATE_HEIGHT = 19;
    const DM_STATE_BORDER_RADIUS = 20;
    const DM_STATE_IMAGE = 21;
    const DM_SLIDER_DECIMAL_DIGITS = 22;
    const DM_SLIDER_DECIMAL_DIGITS2 = 23;

    const DM_RANGE_STEP = 24;
    const DM_SORT_METHOD = 25;
    const DM_SHOW_MORE_METHOD = 26;
    const DM_OPERATION = 27;
    const DM_HELP = 28;
    const DM_SHOW_IN = 29;
    const DM_THOUSAND_SEPARATOR = 30;

    const DM_COLLAPSEABLE = 32;
    const DM_HELP_WIDTH = 33;

    const DM_IS_REVERSE = 34;

    const DM_INCLUDE_IN_URL = 35;
    const DM_URL_POSITION = 36;

    const DM_DISABLE_NO_RESULT_OPTIONS = 37;
    const DM_MIN_MAX_SLIDER_ROLE = 38;
    const DM_COLOR_STATE_DISPLAY = 39;
    const DM_MIN_SLIDER_CODE = 40;

    const DM_SHOW_OPTION_SEARCH = 41;
    const DM_INCLUDE_IN_CANONICAL_URL = 42;

    #endregion

    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_init(strtolower('Mana_Filters/Filter2'), 'id');
        $this->_isPkAutoIncrement = false;
    }   
	protected function _getReplicationSources() {
		return array('eav/attribute');
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationUpdateSelects($target, $options) {
		/* @var $select Varien_Db_Select */ $select = $options['db']->select();
		$select
			->from(array('eav_attribute' => Mage::getSingleton('core/resource')->getTableName('eav/attribute')), null)
			->joinInner(array('eav_attribute_additional' => Mage::getSingleton('core/resource')->getTableName('catalog/eav_attribute')), 
				'eav_attribute.attribute_id = eav_attribute_additional.attribute_id', null)
			->joinInner(array('eav_entity_type' => Mage::getSingleton('core/resource')->getTableName('eav/entity_type')), 
				'eav_attribute.entity_type_id = eav_entity_type.entity_type_id', null)
			->joinInner(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				'target.code = eav_attribute.attribute_code', 
				array('target.id AS id', 'target.code AS code', 'target.type AS type'))
			->distinct()
			->where('eav_entity_type.entity_type_code = ?', 'catalog_product')
			->where('eav_attribute_additional.is_filterable <> 0')
			->columns(array(
				'target.default_mask0 AS default_mask0',
                'target.default_mask1 AS default_mask1',
                'eav_attribute_additional.is_filterable AS is_enabled',
				'eav_attribute.frontend_label AS name',
				'eav_attribute_additional.is_filterable_in_search AS is_enabled_in_search',
				'eav_attribute_additional.position AS position',
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
		}
		$target->setSelect('main', $select);
		
		$select = $options['db']->select()
			->from(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				array('target.id AS id', 'target.code AS code', "target.type AS type"))
			->where("target.code = ?", 'category')
			->columns(array(
				'target.default_mask0 AS default_mask0',
				'target.default_mask1 AS default_mask1',
				'(1) AS is_enabled',
				"('Category') AS name",
				'(1) AS is_enabled_in_search',
				'(-1) AS position',
			));
		if ($options['trackKeys']) {
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('(1 <> 1)');
				$target->setIsKeyFilterApplied(true);
			} 
			if (($keys = $options['targets'][$this->getEntityName()]->getSavedKeys()) && count($keys)) {
				$select->where('target.id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
		}
		$target->setSelect('category', $select);
		
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
			->setCode($values['code'])
			->setType($values['type'])
			->setData('_m_prevent_replication', true);
			
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_IS_ENABLED)) {
			$object->setIsEnabled($values['is_enabled']);
		}
		if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_DISPLAY)) {
			$object->setDisplay(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/'.$object->getType()));
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
            $object->setSortMethod(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/sort_method'));
        }
        if (!Mage::helper('mana_db')->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_DISABLE_NO_RESULT_OPTIONS)) {
            $object->setDisableNoResultOptions(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/disable_no_result_options'));
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
			->joinInner(array('eav_attribute_additional' => Mage::getSingleton('core/resource')->getTableName('catalog/eav_attribute')), 
				'eav_attribute.attribute_id = eav_attribute_additional.attribute_id', null)
			->joinInner(array('eav_entity_type' => Mage::getSingleton('core/resource')->getTableName('eav/entity_type')), 
				'eav_attribute.entity_type_id = eav_entity_type.entity_type_id', null)
			->joinLeft(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				'target.code = eav_attribute.attribute_code', null)
			->distinct()
			->where('eav_entity_type.entity_type_code = ?', 'catalog_product')
			->where('eav_attribute_additional.is_filterable <> 0')
			->where('target.id IS NULL')
			->columns(array(
				'eav_attribute.attribute_code AS code',
				"IF(eav_attribute.attribute_code = 'category', 'category', IF(eav_attribute.attribute_code = 'price', 'price', IF(eav_attribute.backend_type = 'decimal', 'decimal', 'attribute'))) AS type",
				'eav_attribute_additional.is_filterable AS is_enabled',
				'eav_attribute.frontend_label AS name',
				'eav_attribute_additional.is_filterable_in_search AS is_enabled_in_search',
				'eav_attribute_additional.position AS position',
			));
			
		if ($options['trackKeys']) {
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('eav_attribute.attribute_id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
		}
		$target->setSelect('main', $select);
		
		$select = $options['db']->select()
			->from(array('source' => $options['db']->select()->from(array(), "('category') AS code")), null)
			->joinLeft(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 
				"target.code = 'category'", null)
			->where('target.id IS NULL')
			->columns(array(
				"('category') AS code",
				"('category') AS type",
				'(1) AS is_enabled',
				"('Category') AS name",
				'(1) AS is_enabled_in_search',
				'(-1) AS position',
			));
		if ($options['trackKeys']) {
			if (($keys = $options['targets']['eav/attribute']->getSavedKeys()) && count($keys)) {
				$select->where('(1 <> 1)');
				$target->setIsKeyFilterApplied(true);
			} 
		}
		$target->setSelect('category', $select);
		
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Object $object
	 * @param array $values
	 * @param array $options
	 */
	protected function _processReplicationInsert($object, $values, $options) {
		$object
			->setCode($values['code'])
			->setType($values['type'])
			->setData('_m_prevent_replication', true);
			
		$object->setIsEnabled($values['is_enabled']);
		$object->setDisplay(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/'.$object->getType()));
		$object->setName($values['name']);
		$object->setIsEnabledInSearch($values['is_enabled_in_search']);
		$object->setPosition($values['position']);
        $object->setSortMethod(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/sort_method'));
        $object->setDisableNoResultOptions(Mage::helper('mana_db')->getLatestConfig('mana_filters/display/disable_no_result_options'));
    }
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationDeleteSelects($target, $options) {
		if ($options['trackKeys']) {
			if (($keys = $options['targets']['eav/attribute']->getDeletedKeys()) && count($keys)) {
				$attributeJoin = '';
				//$attributeJoin = ' AND '.$options['db']->quoteInto('eav_attribute.attribute_id IN (?)', $keys);
				$target->setIsKeyFilterApplied(true);
			} 
			else {
				$attributeJoin = '';
			}
		}
		else {
			$attributeJoin = '';
		}
		
		/* @var $select Varien_Db_Select */ $select = $options['db']->select();
		$select
			->from(array('target' => Mage::getSingleton('core/resource')->getTableName($this->getEntityName())), 'target.id AS id')
			->joinLeft(array('eav_attribute' => Mage::getSingleton('core/resource')->getTableName('eav/attribute')), 
				'target.code = eav_attribute.attribute_code'.$attributeJoin, null)
			->joinLeft(array('eav_attribute_additional' => Mage::getSingleton('core/resource')->getTableName('catalog/eav_attribute')), 
				'eav_attribute.attribute_id = eav_attribute_additional.attribute_id', null)
			->joinLeft(array('eav_entity_type' => Mage::getSingleton('core/resource')->getTableName('eav/entity_type')),
				"eav_entity_type.entity_type_id = eav_attribute.entity_type_id AND eav_entity_type.entity_type_code = 'catalog_product'", null)
			->distinct()
			->where('(eav_attribute.attribute_id IS NULL) OR (eav_attribute_additional.is_filterable = 0)')
			->where("target.code <> 'category'");
		$target->setSelect('main', $select);
	}
	/**
	 * Enter description here ...
	 * @param array $values
	 * @param array $options
	 */
	protected function _processReplicationDelete($values, $options) {
		if (count($values)) {
			$values = implode(',', $values);
			$table = Mage::getSingleton('core/resource')->getTableName($this->getEntityName());
			$options['db']->query("DELETE FROM {$table} WHERE id IN ($values)");
		}
	}

	protected function _addEditedData($object, $fields, $useDefault) {
		Mage::helper('mana_db')->updateDefaultableField($object, 'is_enabled', Mana_Filters_Resource_Filter2::DM_IS_ENABLED, $fields, $useDefault);
		Mage::helper('mana_db')->updateDefaultableField($object, 'display', Mana_Filters_Resource_Filter2::DM_DISPLAY, $fields, $useDefault);
		Mage::helper('mana_db')->updateDefaultableField($object, 'name', Mana_Filters_Resource_Filter2::DM_NAME, $fields, $useDefault);
		Mage::helper('mana_db')->updateDefaultableField($object, 'is_enabled_in_search', Mana_Filters_Resource_Filter2::DM_IS_ENABLED_IN_SEARCH, $fields, $useDefault);
		Mage::helper('mana_db')->updateDefaultableField($object, 'position', Mana_Filters_Resource_Filter2::DM_POSITION, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'sort_method', Mana_Filters_Resource_Filter2::DM_SORT_METHOD, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'operation', Mana_Filters_Resource_Filter2::DM_OPERATION, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'is_reverse', Mana_Filters_Resource_Filter2::DM_IS_REVERSE, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'disable_no_result_options', Mana_Filters_Resource_Filter2::DM_DISABLE_NO_RESULT_OPTIONS, $fields, $useDefault);
    }
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        if ($edit = $object->getValueData()) {
            foreach ($edit['saved'] as $id => $editId) {
                if ($id > 0) {
                    $editModel = Mage::helper('mana_admin')->loadModel('mana_filters/filter2_value', $editId);
                    $data = $editModel->getData();
                    unset($data['id']);
                    unset($data['edit_status']);
                    unset($data['edit_session_id']);
                    $model = Mage::helper('mana_admin')->loadModel('mana_filters/filter2_value', $id)->addData($data);
                    // validation code here
                    $model->save();
                    $editModel->delete();
                }
                else {
                    // inserts
                    throw new Exception('Not implemented!');
                }
            }
            foreach ($edit['deleted'] as $id) {
                // deletes
                throw new Exception('Not implemented!');
            }
        }
        $object->unsValueData();
        $object->setHadValueData(true);
        return parent::_afterSave($object);
    }

    /**
     * @param Mana_Filters_Model_Filter2 $filter
     * @return bool|int
     */
    public function getAttributeId($filter) {
        $db = $this->_getReadAdapter();

        if ($filter->getType() != 'category') {
            return $db->fetchOne($db->select()
                ->from(array('a' => $this->getTable('eav/attribute')), 'attribute_id')
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
                ->where('a.attribute_code = ?', $filter->getCode()));
        }
        else {
            return false;
        }
    }
}