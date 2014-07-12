<?php
/** 
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Filters_Block_Layer extends Mage_Core_Block_Template {
    public function setCategoryId($id)
    {
        $category = Mage::getModel('catalog/category')->load($id);
        if ($category->getId()) {
            Mage::getSingleton('catalog/layer')->setCurrentCategory($category);
        }
    }
}