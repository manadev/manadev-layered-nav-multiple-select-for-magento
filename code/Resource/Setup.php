<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Eav enabled class which is used by setup scripts of this module
 * @author Mana Team
 *
 */
class Mana_Filters_Resource_Setup extends Mana_Core_Resource_Eav_Setup {
	public function getDefaultEntities() {
		return array('m_filter' => array(
			'entity_model' => 'mana_filters/filter', // model class
			'table' => 'mana_filters/filter', // base table name
            'additional_attribute_table' => 'mana_core/attribute',
            'entity_attribute_collection' => 'mana_filters/filter_attribute_collection',
			
			'attribute_model' => '', // 1.6 fix
			'increment_model' => '', // 1.6 fix
			'table_prefix' => '', // 1.6 fix
			'id_field' => '', // 1.6 fix
			'attributes' => array(
				'is_enabled' => array(
					// storage
                    'type'              => 'int',
                    'default'           => '',
					'is_global'			=> Mana_Core_Model_Attribute_Scope::_STORE, 
					
					// editing
					'label'             => 'Is Visible In Category',
					'input'				=> 'select', // dropdown combobox
					'source'            => 'mana_filters/source_filterable',
					'required'          => true,
		
					// default chain
					'has_default'		=> true,
					'default_model'		=> 'mana_filters/filter_default',
					'default_mask'		=> 0x0000000000000001,
				),
				'is_enabled_in_search' => array(
					// storage
                    'type'              => 'int',
                    'default'           => '',
					'is_global'			=> Mana_Core_Model_Attribute_Scope::_STORE, 
					
					// editing
					'label'             => 'Is Visible In Search',
					'input'				=> 'select', // dropdown combobox
					'source'            => 'mana_filters/source_filterable',
					'required'          => true,
		
					// default chain
					'has_default'		=> true,
					'default_model'		=> 'mana_filters/filter_default',
					'default_mask'		=> 0x0000000000000010,
				),
				'position' => array(
					// storage
                    'type'              => 'int',
                    'default'           => '',
					'is_global'			=> Mana_Core_Model_Attribute_Scope::_STORE, 
					
					// editing
					'label'             => 'Position',
					'note'				=> 'All filters are shown in layered navigation ordered by position',
					'input'				=> 'text', // dropdown combobox
					'required'          => true,
		
					// default chain
					'has_default'		=> true,
					'default_model'		=> 'mana_filters/filter_default',
					'default_mask'		=> 0x0000000000000020,
				),
				'display' => array(
					// storage
                    'type'              => 'varchar',
                    'default'           => '',
					'is_global'			=> Mana_Core_Model_Attribute_Scope::_STORE, 
									
					// editing
					'label'             => 'Display As',
					'input'				=> 'select', // dropdown combobox
					'source'            => 'mana_filters/source_display',
					'required'          => true,
				
					// default chain
					'has_default'		=> true,
					'default_model'		=> 'mana_filters/config_display_default',
					'default_source'	=> 'mana_filters/display/%s',
					'default_mask'		=> 0x0000000000000002,
				),
				'code' => array(
					// storage
                    'type'              => 'static',
                    'default'           => '',
					'is_global'			=> Mana_Core_Model_Attribute_Scope::_GLOBAL, 
					'is_key'			=> true,
													
					// editing
					'input'				=> 'hidden', 
				),
				'default_mask0' => array(
					// storage
                    'type'              => 'static',
                    'default'           => '',
					'is_global'			=> Mana_Core_Model_Attribute_Scope::_GLOBAL, 
													
					// editing
					'input'				=> 'hidden', 
				),
				'name' => array(
					// storage
                    'type'              => 'varchar',
                    'default'           => '',
					'is_global'			=> Mana_Core_Model_Attribute_Scope::_STORE, 
									
					// editing
					'label'             => 'Name',
					'input'				=> 'text', 
					'required'          => true,

					// default chain
					'has_default'		=> true,
					'default_model'		=> 'mana_filters/filter_default',
					'default_mask'		=> 0x0000000000000004,
				),
			), 
		));
	}
}