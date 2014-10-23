<?php
/**
 * Layer filter category rewrite.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
    /**
     * @var array
     */
    protected $_appliedCategoryIds = array();

    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $param = $request->getParam($this->getRequestVar());
        if (null === $param || '' === $param) {
            return $this;
        }
        $categoryIds = explode(Bubble_Layer_Helper_Data::OPTIONS_SEPARATOR, $param);
        if (!empty($categoryIds)) {
            if ($this->_helper()->isSeoEnabled()) {
                $currentCategory = $this->getLayer()->getCurrentCategory();
                // If SEO is enabled, category id has been transformed to category key
                foreach ($categoryIds as &$categoryId) {
                    $categoryId = $this->_helper()->getCategoryId($categoryId, $currentCategory);
                }
                unset($categoryId);
            }

            foreach ($categoryIds as $categoryId) {
                $category = Mage::getModel('catalog/category')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($categoryId);
                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($category->getName(), $categoryId)
                );
            }

            $this->_appliedCategoryIds = $categoryIds;

            // Register current category filter to handle product collection later
            Mage::register('bubble_layer_applied_category_ids', $this->_appliedCategoryIds);
        }

        return $this;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $category   = $this->getCategory();
            /** @var $category Mage_Catalog_Model_Category */
            $categories = $category->getChildrenCategories();

            /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = $this->getLayer()->getProductCollection();

            $countSelect = $collection->getProductCountSelect();
            $fromPart = $countSelect->getPart(Zend_Db_Select::FROM);
            foreach ($fromPart as $key => &$part) {
                if ($key == 'count_table') {
                    $visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();
                    $part['joinCondition'] .= $collection
                        ->getConnection()
                        ->quoteInto(' AND count_table.visibility IN (?)', $visibility);
                } elseif ($key == 'cat_index') {
                    $part['joinType'] = Zend_Db_Select::LEFT_JOIN;
                }
            }
            unset($part);
            $countSelect->reset(Zend_Db_Select::FROM);
            $countSelect->setPart(Zend_Db_Select::FROM, $fromPart);

            $collection->addCountToCategories($categories);

            $data = array();
            foreach ($categories as $category) {
                if (in_array($category->getId(), $this->_appliedCategoryIds) ||
                    $category->getIsActive() && $category->getProductCount())
                {
                    $data[] = array(
                        'label' => Mage::helper('core')->escapeHtml($category->getName()),
                        'value' => $category->getId(),
                        'count' => $category->getProductCount(),
                    );
                }
            }
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getRequestVar()
    {
        return $this->_helper()->getCategoryRequestVar();
    }

    /**
     * @return Bubble_Layer_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('bubble_layer');
    }
}