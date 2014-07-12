<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class Mana_Filters_Model_Observer {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Raises flag is config value changed this module's replicated tables rely on (handles event "m_db_is_config_changed")
	 * @param Varien_Event_Observer $observer
	 */
	public function isConfigChanged($observer) {
		/* @var $result Varien_Object */ $result = $observer->getEvent()->getResult();
		/* @var $configData Mage_Core_Model_Config_Data */ $configData = $observer->getEvent()->getConfigData();
		
		Mage::helper('mana_db')->checkIfPathsChanged($result, $configData, array(
			'mana_filters/display/attribute',
			'mana_filters/display/price',
			'mana_filters/display/category',
			'mana_filters/display/decimal',
            'mana_filters/display/sort_method',
            'mana_filters/display/disable_no_result_options',
		));
	}
	/**
	 * REPLACE THIS WITH DESCRIPTION (handles event "catalog_entity_attribute_save_commit_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function afterCatalogAttributeSave($observer) {
        $dataObject = $observer->getEvent()->getDataObject();

        if (!$dataObject->getdata('_m_prevent_replication') && $dataObject->getIsFilterable() == 0) {
            $filter = Mage::getModel('mana_filters/filter2')->load($dataObject->getAttributeCode(), 'code');
            if ($filter->getId()) {
                $filter->delete();
            }
        }
	}
	/**
	 * REPLACE THIS WITH DESCRIPTION (handles event "prepare_catalog_product_index_select")
	 * @param Varien_Event_Observer $observer
	 */
	public function fixAttributeIndexerSelectForConfigurableProductDefaultValues($observer) {
        if (Mage::helper('mana_core')->isMageVersionEqualOrGreater('1.7')) {
            return;
        }

        /* @var $select Varien_Db_Select */ $select = $observer->getEvent()->getSelect();
		/* @var $entityField Zend_Db_Expr */ $entityField = $observer->getEvent()->getEntityField();

        /* @var $res Mage_Core_Model_Resource */ $res = Mage::getSingleton('core/resource');

        if (in_array((string)$entityField, array('pvd.entity_id', 'pid.entity_id', '`pvd`.`entity_id`', '`pid`.`entity_id`'))) {
            $select
                ->joinInner(
                    array('m_configurable_product' => $res->getTableName('catalog/product')),
                    'm_configurable_product.entity_id = '.$entityField, null)
                ->joinInner(
                    array('m_configurable_attribute' => $res->getTableName('catalog/eav_attribute')),
                    'm_configurable_attribute.attribute_id = ' . str_replace('entity_id', 'attribute_id', (string)$entityField), null)
                ->joinInner(
                    array('m_set_relation' => $res->getTableName('eav/entity_attribute')),
                    'm_set_relation.attribute_set_id = m_configurable_product.attribute_set_id AND m_set_relation.attribute_id = m_configurable_attribute.attribute_id', null)
                ->where("NOT((m_configurable_product.type_id = 'configurable') AND (m_configurable_attribute.is_configurable = 1))");
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_to_html_after")
     * @param Varien_Event_Observer $observer
     */
    public function hideCmsContentOnFilteredPages($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
        /* @var $transport Varien_Object */
        $transport = $observer->getEvent()->getData('transport');

        if ($block->getNameInLayout() == 'cms_page' &&
            ($block->getData('hide_cms_content_when_filters_applied') || Mage::getStoreConfigFlag('mana_filters/display/hide_cms_page_content')) &&
            in_array($this->coreHelper()->getRoutePath(), array('cms/page/view', 'cms/index/index')) &&
            ($state = $this->filterHelper()->getLayer()->getState()) &&
            $state->getFilters())

        {
            $transport->setData('html', '');
        }
        // INSERT HERE: event handler code
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }
    #endregion
}