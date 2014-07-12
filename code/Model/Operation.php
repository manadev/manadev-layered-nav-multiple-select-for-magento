<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Filters_Model_Operation extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => '', 'label' => Mage::helper('mana_filters')->__('Logical OR')),
            array('value' => 'and', 'label' => Mage::helper('mana_filters')->__('Logical AND')),
        );
    }
}