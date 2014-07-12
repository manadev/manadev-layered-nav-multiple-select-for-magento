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
 * @method string getType()
 */
class Mana_Filters_Model_Filter2 extends Mana_Db_Model_Object {
    protected $_eventPrefix = 'mana_filter';
    protected $_entity = 'mana_filters/filter2';

    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
	protected function _construct() {
		$this->_init(strtolower('Mana_Filters/Filter2'));
	}
	public function getDisplayOptions() {
		return Mage::getConfig()->getNode('mana_filters/display/'.$this->getType().'/'.$this->getDisplay());	
	}
	public function getAttribute() {
		if ($this->getCode() == 'category') {
			return null;
		}
		if (!$this->hasData('attribute')) {
			/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
			$collection = Mage::getSingleton('mana_filters/filter_default')->getFilterableAttributes($this->getStoreId());
			$attribute = $core->collectionFind($collection, 'attribute_code', $this->getCode());
			$this->setAttribute($attribute);
		}
		return $this->getData('attribute'); 
	}

	protected function _validate($result) {
		$t = Mage::helper('mana_filters');
		if (trim($this->getIsEnabled()) === '') {
			$result->addError($t->__('Please fill in %s field', $t->__('In Category')));
		}
		if (trim($this->getDisplay()) === '') {
			$result->addError($t->__('Please fill in %s field', $t->__('Display As')));
		}
		if (trim($this->getName()) === '') {
			$result->addError($t->__('Please fill in %s field', $t->__('Name')));
		}
		if (trim($this->getIsEnabledInSearch()) === '') {
			$result->addError($t->__('Please fill in %s field', $t->__('In Search')));
		}
		if (trim($this->getPosition()) === '') {
			$result->addError($t->__('Please fill in %s field', $t->__('Position')));
		}
	}

	public function validateDetails() {
        if ($edit = $this->getValueData()) {
            foreach ($edit['saved'] as $id => $editId) {
                if ($id > 0) {
                    $editModel = Mage::helper('mana_admin')->loadModel('mana_filters/filter2_value', $editId);
                    $editModel->validate();
                }
                else {
                    // inserts
                    throw new Exception('Not implemented!');
                }
            }
        }
	}

    public function getCode() {
        return isset($this->_data['code']) ? $this->_data['code'] : null;
    }

    /**
     * Init indexing process after category data commit
     *
     * @return Mage_Catalog_Model_Category
     */
    public function afterCommitCallback() {
        parent::afterCommitCallback();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, $this->_entity, Mage_Index_Model_Event::TYPE_SAVE);
        }

        return $this;
    }

    #region Dependencies
    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexerSingleton() {
        return Mage::getSingleton('index/indexer');
    }
    #endregion
}
