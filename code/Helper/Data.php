<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for Mana_Filters module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Filters_Helper_Data extends Mana_Core_Helper_Layer {
	/**
	 * Return unique filter name. 
	 * OO purists would say that kind of ifs should be done using virtual functions. Here we ignore OO-ness and 
	 * micro performance penalty for the sake of clarity and keeping logic in one file.
	 * @param Mage_Catalog_Model_Layer_Filter_Abstract $model
	 * @return string
	 */
	public function getFilterName($block, $model) {
		if ($model instanceof Mana_Filters_Model_Filter_Category) {
            $result = 'category';
		}
		else {
            $result = $model->getAttributeModel()->getAttributeCode();
        }

        if ($showInFilter = $block->getShowInFilter()) {
            return $showInFilter . '_' . $result;
        } else {
            return $result;
        }
    }
	// INSERT HERE: helper functions that should be available from any other place in the system
	public function getJsPriceFormat() {
		return $this->formatPrice(0);
	}
	public function formatPrice($price) {
		$store = Mage::app()->getStore();
        if ($store->getCurrentCurrency()) {
            return $store->getCurrentCurrency()->formatPrecision($price, 0, array(), false, false);
        }
        return $price;
	}
	
	protected $_filterOptionsCollection;
    protected $_filterSearchOptionsCollection;
    protected $_filterAllOptionsCollection;
	public function getFilterOptionsCollection($allCategories = false) {
	    $request = Mage::app()->getRequest();
	    if ($request->getModuleName() == 'catalogsearch' && $request->getControllerName() == 'result' && $request->getActionName() == 'index' ||
	        $request->getModuleName() == 'manapro_filterajax' && $request->getControllerName() == 'search' && $request->getActionName() == 'index')
	    {
            if (!$this->_filterSearchOptionsCollection) {
                Mana_Core_Profiler2::start(__METHOD__ . "::search");
                $this->_filterSearchOptionsCollection = Mage::getResourceModel('mana_filters/filter2_store_collection')
                        ->addColumnToSelect('*')
                        ->addStoreFilter(Mage::app()->getStore())
                        ->setOrder('position', 'ASC');
                Mage::dispatchEvent('m_before_load_filter_collection', array('collection' => $this->_filterSearchOptionsCollection));
                if (Mana_Core_Profiler2::enabled()) {
                    $this->_filterSearchOptionsCollection->load();
                    Mana_Core_Profiler2::stop();
                }
            }
            return $this->_filterSearchOptionsCollection;
        }
		elseif ($allCategories) {
            if (!$this->_filterAllOptionsCollection) {
                Mana_Core_Profiler2::start(__METHOD__ . "::all");
                $this->_filterAllOptionsCollection = Mage::getResourceModel('mana_filters/filter2_store_collection')
		        	->addColumnToSelect('*')
		        	->addStoreFilter(Mage::app()->getStore())
		        	->setOrder('position', 'ASC');
                Mage::dispatchEvent('m_before_load_filter_collection', array('collection' => $this->_filterAllOptionsCollection));
                if (Mana_Core_Profiler2::enabled()) {
                    $this->_filterAllOptionsCollection->load();
                    Mana_Core_Profiler2::stop();
                }
            }
			return $this->_filterAllOptionsCollection;
		}
		else {
			if (!$this->_filterOptionsCollection) {
			    Mana_Core_Profiler2::start(__METHOD__ . "::category");
				$setIds = Mage::getSingleton('catalog/layer')->getProductCollection()->getSetIds();
				$this->_filterOptionsCollection = Mage::getResourceModel('mana_filters/filter2_store_collection')
		        	->addFieldToSelect('*')
		        	->addCodeFilter($this->_getAttributeCodes($setIds))
                    ->addStoreFilter(Mage::app()->getStore())
		        	->setOrder('position', 'ASC');
                Mage::dispatchEvent('m_before_load_filter_collection', array('collection' => $this->_filterOptionsCollection));
                if (Mana_Core_Profiler2::enabled()) {
                    $this->_filterOptionsCollection->load();
                    Mana_Core_Profiler2::stop();
                }
            }
            return $this->_filterOptionsCollection;
		}
	}
	protected function _getAttributeCodes($setIds) {
		/* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */ 
		$collection = Mage::getResourceModel('catalog/product_attribute_collection');
		$collection->setAttributeSetFilter($setIds);
		$select = $collection->getSelect()
			->reset(Zend_Db_Select::COLUMNS)
			->columns('attribute_code');
		return array_merge($collection->getConnection()->fetchCol($select), array('category'));
	}
	public function markLayeredNavigationUrl($url, $routePath, $routeParams) {
	    $request = Mage::app()->getRequest();
	    $path = $request->getModuleName().'/'.$request->getControllerName(). '/'.$request->getActionName();
        if ($path == 'catalog/category/view') {
            if (Mage::getStoreConfigFlag('mana_filters/session/save_applied_filters')) {
                $url .= (strpos($url, '?') === false) ? '?m-layered=1' : '&m-layered=1';
            }
        }
        elseif ($path == 'catalogsearch/result/index') {
            if (Mage::getStoreConfigFlag('mana_filters/session/save_applied_search_filters')) {
                $url .= (strpos($url, '?') === false) ? '?m-layered=1' : '&m-layered=1';
            }
        }
        else {
            if (Mage::getStoreConfigFlag('mana_filters/session/save_applied_cms_filters')) {
                $url .= (strpos($url, '?') === false) ? '?m-layered=1' : '&m-layered=1';
            }
        }
		return $url;
	}
    public function getClearUrl($markUrl = true, $clearListParams = false, $nosid = false, $clearAllParams = false) {
        $filterState = array('p' => null);
        foreach ($this->getLayer()->getState()->getFilters() as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }
        if ($clearListParams) {
            $filterState = array_merge($filterState, array(
              'dir' => null,
              'order' => null,
              'p' => null,
              'limit' => null,
              'mode' => null,
            ));
        }
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $filterState['m-layered'] = null;
        $params['_query'] = $filterState;
        if ($nosid) {
            $params['_nosid'] = true;
        }
        $result = Mage::getUrl('*/*/*', $params);
        if ($clearAllParams) {
            /* @var $mbstring Mana_Core_Helper_Mbstring */
            $mbstring = Mage::helper('mana_core/mbstring');

            if ($pos = $mbstring->strpos($result, '?')) {
                $result = $mbstring->substr($result, 0, $pos);
            }
        }
        elseif ($markUrl) {
            $result = $this->markLayeredNavigationUrl($result, '*/*/*', $params);
        }
        return $result;
    }
    public function getActiveFilters() {
        $filters = $this->getLayer()->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = array();
        }
        return $filters;
    }
    public function resetProductCollectionWhereClause($select) {
        $preserved = new Varien_Object(array('preserved' => array()));
        $where = $select->getPart(Zend_Db_Select::WHERE);
        Mage::dispatchEvent('m_preserve_product_collection_where_clause', compact('where', 'preserved'));
        $preserved = $preserved->getPreserved();
        if (Mage::helper('mana_core')->isMageVersionEqualOrGreater('1.7')) {
            foreach ($where as $key => $condition) {
                if (strpos($condition, 'e.website_id = ') !== false || strpos($condition, '`e`.`website_id` = ') !== false) {
                    $preserved[$key] = $key;
                }
                if (strpos($condition, 'e.customer_group_id = ') !== false || strpos($condition, '`e`.`customer_group_id` = ') !== false) {
                    $preserved[$key] = $key;
                }
            }

        }
        foreach ($where as $key => $condition) {
            if (!in_array($key, $preserved)) {
                unset($where[$key]);
            }
        }
        $where = array_values($where);
        if (isset($where[0]) && strpos($where[0], 'AND ') === 0) {
            $where[0] = substr($where[0], strlen('AND '));
        }
        $select->setPart(Zend_Db_Select::WHERE, $where);
    }

    /**
     * @param Mana_Filters_Model_Filter2_Store $filterOptions
     * @throws Exception
     * @return bool
     */
    public function isFilterEnabled($filterOptions) {
        switch ($this->getMode()) {
            case 'category':
                return $filterOptions->getIsEnabled();
            case 'search':
                return $filterOptions->getIsEnabledInSearch();
            default:
                throw new Exception('Not implemented');
        }
    }

    public function canShowFilterInBlock($block, $filter) {
        if ($block->getData('show_'.$filter->getCode())) {
            return true;
        }
        elseif ($block->getData('hide_' . $filter->getCode())) {
            return false;
        }
        elseif ($block->getData('show_all_filters')) {
            return true;
        }
        elseif ($block->getData('hide_all_filters')) {
            return false;
        }
        elseif ($showInFilter = $block->getShowInFilter()) {
            $showIn = $filter->getShowIn();
            if (!is_array($showIn)) {
                $showIn = explode(',', $showIn);
            }
            if (in_array($showInFilter, $showIn)) {
                return true;
            }
            if ($this->isMobileFilter($block, $filter))
            {
                return true;
            }
            return false;
        }
        else {
            return true;
        }
    }
    public function isMobileFilter($block, $filter) {
        if ($showInFilter = $block->getShowInFilter()) {
            $showIn = $filter->getShowIn();
            if (!is_array($showIn)) {
                $showIn = explode(',', $showIn);
            }
            if (in_array(Mage::getStoreConfig('mana_filters/mobile/column_filters'), array('copy', 'move')) &&
                $showInFilter == 'above_products' && !in_array('above_products', $showIn)
            ) {
                return true;
            }
        }
        return false;
    }
    public function getFilterLayoutName($block, $filter) {
        if ($showInFilter = $block->getShowInFilter()) {
            return 'm_' . $showInFilter . '_' . $filter->getCode() . '_filter';
        }
        else {
            return 'm_' . $filter->getCode() . '_filter';
        }
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection
     * @param $categoryCollection
     * @param bool $inCurrentCategory
     * @return $this
     */
    public function addCountToCategories($productCollection, $categoryCollection, $inCurrentCategory = false) {
        Mana_Core_Profiler2::start(__METHOD__);
        $isAnchor = array();
        $isNotAnchor = array();
        foreach ($categoryCollection as $category) {
            if ($category->getIsAnchor()) {
                $isAnchor[] = $category->getId();
            } else {
                $isNotAnchor[] = $category->getId();
            }
        }
        $productCounts = array();
        if ($isAnchor || $isNotAnchor) {
            /* @var $select Varien_Db_Select */
            $select = $productCollection->getProductCountSelect();

            if ($inCurrentCategory) {
                $from = $select->getPart(Varien_Db_Select::FROM);
                if (isset($from['cat_index'])) {
                    $categoryId = $this->getLayer()->getCurrentCategory()->getId();
                    $from['cat_index']['joinCondition'] = preg_replace(
                        "/(.*)(`?)cat_index(`?).(`?)category_id(`?)='(\\d+)'(.*)/",
                        "$1$2cat_index$3.$4category_id$5='{$categoryId}'$7",
                        $from['cat_index']['joinCondition']
                    );
                    $select->setPart(Varien_Db_Select::FROM, $from);
                }
            }

            Mage::dispatchEvent(
                'catalog_product_collection_before_add_count_to_categories',
                array('collection' => $productCollection)
            );

            if ($isAnchor) {
                $anchorStmt = clone $select;
                $anchorStmt->limit(); //reset limits
                $anchorStmt->where('count_table.category_id IN (?)', $isAnchor);
                $sql = $anchorStmt->__toString();
                $productCounts += $productCollection->getConnection()->fetchPairs($anchorStmt);
                $anchorStmt = null;
            }
            if ($isNotAnchor) {
                $notAnchorStmt = clone $select;
                $notAnchorStmt->limit(); //reset limits
                $notAnchorStmt->where('count_table.category_id IN (?)', $isNotAnchor);
                $notAnchorStmt->where('count_table.is_parent = 1');
                $productCounts += $productCollection->getConnection()->fetchPairs($notAnchorStmt);
                $notAnchorStmt = null;
            }
            $select = null;
            $productCollection->unsProductCountSelect();
        }

        foreach ($categoryCollection as $category) {
            $_count = 0;
            if (isset($productCounts[$category->getId()])) {
                $_count = $productCounts[$category->getId()];
            }
            $category->setProductCount($_count);
        }

        Mana_Core_Profiler2::stop();

        return $this;
    }
    public function renderHtml($block, $part, $data = array()) {
        $result = new Varien_Object();
        switch ($part) {
            case 'groups':
                $result->setResult($this->getNoGroups($block->getFilters()));
                break;
            case 'name_attributes':
                echo ' data-id="' . $this->getFilterLayoutName($block, $data['filter']->getFilterOptions()) . '"';
                break;
            case 'group_attributes':
                echo ' data-id="' . $block->getShowInFilter() .'-'. $data['group']->getId() . '"';
                break;
            case 'menu_visible':
                $result->setResult(false);
                break;
            case 'currently_shopping_by':
                $result->setResult(true);
                break;
            case 'name_action':
            case 'group_action':
                $result->setResult(array());
                break;
        }
        Mage::dispatchEvent('m_advanced_filter_' . $part, array_merge($data, compact('block', 'result')));
        return $result->getResult();
    }
    /**
     * @param $result
     * @param $filterBlocks
     * @return array
     */
    public function getNoGroups($filterBlocks) {
        $result = array(
            '' => new Varien_Object(array(
                'name' => '',
                'sort_order' => -1,
                'id' => 0,
                'filters' => array(),
            ))
        );
        foreach ($filterBlocks as /* @var $filterBlock Mana_Filters_Block_Filter */ $filterBlock) {
            $filters = $result['']->getFilters();
            $filters[] = $filterBlock;
            $result['']->setFilters($filters);
        }
        return $result;
    }

    /**
     * @param string $field
     * @param Mana_Filters_Model_Filter2_Store $options
     * @return string
     */
    public function getFilterTypeName($field, $options) {
        $displayOptions = $options->getDisplayOptions();
        $result = (string)$displayOptions->$field;

        // add Solr prefix
        $prefix = '';
        if ($this->useSolr()) {
            $prefix .= 'solr_';
        }
        if ($prefix) {
            $prefixedField = $prefix.$field;
            if ($prefixedResult = (string)$displayOptions->$prefixedField) {
                $result = $prefixedResult;
            }
        }

        // add prefix for alternative logic
        if ($options->getIsReverse()) {
            $prefix .= 'reverse_';
        }
        elseif ($options->getOperation() == 'and') {
            $prefix .= 'and_';
        }
        if ($prefix) {
            $prefixedField = $prefix.$field;
            if ($prefixedResult = (string)$displayOptions->$prefixedField) {
                $result = $prefixedResult;
            }
        }

        return $result;
    }

    public function isTreeVisible() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        if ($core->isManadevLayeredNavigationTreeInstalled()) {
            $filterCollection = $this->getFilterOptionsCollection(true);
            foreach ($filterCollection as $filter) {
                /* @var $filter Mana_Filters_Model_Filter2_Store */
                if ($filter->getType() == 'category') {
                    if ($filter->getData('display') == 'tree') {
                        return true;
                    }
                }
            }
       }
       return false;
    }

    public function getPageContent() {
        return array(
            'filters' => $this->getActiveFilters(),
            'page' => Mage::app()->getRequest()->getParam('p'),
        );
    }


    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    #endregion
}