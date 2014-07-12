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
class Mana_Filters_Resource_EnterpriseSearch_Engine extends Enterprise_Search_Model_Resource_Engine {
    /**
     * Define if Layered Navigation is allowed
     *
     * @deprecated after 1.9.1 - use $this->isLayeredNavigationAllowed()
     *
     * @return bool
     */
    public function isLeyeredNavigationAllowed() {
        return $this->isLayeredNavigationAllowed();
    }
}