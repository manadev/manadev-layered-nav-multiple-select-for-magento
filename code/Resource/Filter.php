<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Resource for extracting filter settings for specified store.
 * @author Mana Team
 *
 */
class Mana_Filters_Resource_Filter extends Mana_Core_Resource_Eav {
	/**
	 * This method is invoked from constructor to setup resource against database  
	 */
	protected function _construct() {
		parent::_construct();
		$this->setType('m_filter');
	}
	public function getBackendType($attributeCode) {
		return $this->_getReadAdapter()->fetchOne("SELECT backend_type FROM `{$this->getTable('eav/attribute')}` WHERE `attribute_code` = '$attributeCode'");
	}
}