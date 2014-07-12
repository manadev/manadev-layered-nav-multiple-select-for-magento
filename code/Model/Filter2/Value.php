<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Models/DB-backed model */
/**
 * INSERT HERE: what is this model for 
 * @author Mana Team
 */
class Mana_Filters_Model_Filter2_Value extends Mana_Db_Model_Object {
    protected $_eventPrefix = 'mana_filter_value';

    #region bit indexes for default_mask field(s)
    const DM_NAME = Mana_Filters_Resource_Filter2_Value::DM_NAME;
    const DM_POSITION = Mana_Filters_Resource_Filter2_Value::DM_POSITION;
    const DM_COLOR = Mana_Filters_Resource_Filter2_Value::DM_COLOR;
    const DM_NORMAL_IMAGE = Mana_Filters_Resource_Filter2_Value::DM_NORMAL_IMAGE;
    const DM_SELECTED_IMAGE = Mana_Filters_Resource_Filter2_Value::DM_SELECTED_IMAGE;
    const DM_NORMAL_HOVERED_IMAGE = Mana_Filters_Resource_Filter2_Value::DM_NORMAL_HOVERED_IMAGE;
    const DM_SELECTED_HOVERED_IMAGE = Mana_Filters_Resource_Filter2_Value::DM_SELECTED_HOVERED_IMAGE;
    const DM_STATE_IMAGE = Mana_Filters_Resource_Filter2_Value::DM_STATE_IMAGE;

    const DM_CONTENT_IS_ACTIVE = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_IS_ACTIVE;
    const DM_CONTENT_IS_INITIALIZED = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_IS_INITIALIZED;
    const DM_CONTENT_STOP_FURTHER_PROCESSING = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_STOP_FURTHER_PROCESSING;
    const DM_CONTENT_META_TITLE = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_TITLE;
    const DM_CONTENT_META_KEYWORDS = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_KEYWORDS;
    const DM_CONTENT_META_DESCRIPTION = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_DESCRIPTION;
    const DM_CONTENT_META_ROBOTS = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_META_ROBOTS;
    const DM_CONTENT_TITLE = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_TITLE;
    const DM_CONTENT_SUBTITLE = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_SUBTITLE;
    const DM_CONTENT_DESCRIPTION = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_DESCRIPTION;
    const DM_CONTENT_ADDITIONAL_DESCRIPTION = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_ADDITIONAL_DESCRIPTION;
    const DM_CONTENT_LAYOUT_XML = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_LAYOUT_XML;
    const DM_CONTENT_WIDGET_LAYOUT_XML = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_WIDGET_LAYOUT_XML;
    const DM_CONTENT_PRIORITY = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_PRIORITY;
    const DM_CONTENT_COMMON_DIRECTIVES = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_COMMON_DIRECTIVES;
    const DM_CONTENT_BACKGROUND_IMAGE = Mana_Filters_Resource_Filter2_Value::DM_CONTENT_BACKGROUND_IMAGE;

    #endregion
    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
	protected function _construct() {
		$this->_init(strtolower('Mana_Filters/Filter2_Value'));
	}

	public function loadByFilterPosition($filterId, $position) {
	    /* @var $resource Mana_Filters_Resource_Filter2_Value */
	    $resource = $this->_getResource();
        $resource->loadByFilterPosition($this, $filterId, $position);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;

        return $this;
    }
}
