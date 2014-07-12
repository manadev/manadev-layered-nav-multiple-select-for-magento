<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Default value provider which gets value from global configuration, depending on filter type
 * @author Mana Team
 *
 */
class Mana_Filters_Model_Config_Display_Default extends Mana_Core_Model_Config_Default {
	public function getDefaultValue($model, $attributeCode, $path) {
		return Mage::getStoreConfig(sprintf($path, $model->getType()));
	}
}