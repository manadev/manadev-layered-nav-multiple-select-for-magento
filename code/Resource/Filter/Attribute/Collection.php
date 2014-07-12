<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Attribute definition collection for filters
 * @author Mana Team
 *
 */
class Mana_Filters_Resource_Filter_Attribute_Collection extends Mana_Core_Resource_Attribute_Collection {
	public function __construct($resource=null) {
		$this->setEntityType(Mana_Filters_Model_Filter::ENTITY);
		parent::__construct($resource);
	}
}