<?php
/**
 * Global module observer.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Model_Observer
{
    /**
     * Clear cache when an attribute is saved
     */
    public function onAttributeSaveAfter()
    {
        Mage::app()->getCache()->remove(Bubble_Layer_Helper_Data::ATTRIBUTE_LABELS_CACHE_ID);
        Mage::app()->getCache()->remove(Bubble_Layer_Helper_Data::OPTIONS_MAPPING_CACHE_ID);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onCoreBlockToHtmlBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block instanceof Mage_Catalog_Block_Layer_View) {
            foreach ($block->getFilters() as $filter) {
                $tpl = 'bubble/layer/catalog/layer/filter.phtml';
                if (Mage::helper('bubble_layer')->isPriceSliderEnabled()
                    && ($filter instanceof Mage_Catalog_Block_Layer_Filter_Price
                        || $filter instanceof Bubble_Search_Block_Catalog_Layer_Filter_Price)
                ) {
                    $tpl = 'bubble/layer/catalog/layer/filter/price.phtml';
                }
                $filter->setTemplate($tpl);
            }
        }
    }

    /**
     * @return bool
     */
    public function handleAjaxLayer()
    {
        $request = Mage::app()->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return false;
        }

        $layout = Mage::app()->getLayout();
        $output = array(
            'title'     => $layout->getBlock('head')->getTitle(),
            'content'   => $layout->getBlock('content')->toHtml(), // Careful! 'content' BEFORE 'left' is important
            'left'      => $layout->getBlock('left')->toHtml(),
        );

        Mage::app()->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($output))
            ->sendResponse();
        exit;
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onProductCollectionApplyLimitationsAfter(Varien_Event_Observer $observer)
    {
        $categoryIds = Mage::registry('bubble_layer_applied_category_ids');
        if (!empty($categoryIds)) {
            $collection = $observer->getEvent()->getCollection();
            $select     = $collection->getSelect();
            $colsPart   = $select->getPart(Zend_Db_Select::COLUMNS);
            foreach ($colsPart as $k => $col) {
                if (isset($col[2]) && $col[2] == 'cat_index_position') {
                    unset($colsPart[$k]);
                }
            }
            $select->setPart(Zend_Db_Select::COLUMNS, $colsPart);

            $fromPart = $select->getPart(Zend_Db_Select::FROM);

            if (isset($fromPart['cat_index'])) {
                unset($fromPart['cat_index']);
            }
            $select->setPart(Zend_Db_Select::FROM, $fromPart);

            $resource       = Mage::getResourceModel('catalog/category_indexer_product');
            $connection     = $resource->getReadConnection();
            $visibilityIds  = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();
            $conditions     = array(
                'e.entity_id = cat_index.product_id',
                $connection->quoteInto('cat_index.category_id IN (?)', $categoryIds),
                $connection->quoteInto('cat_index.visibility IN (?)', $visibilityIds),
                $connection->quoteInto('cat_index.store_id = ?', Mage::app()->getStore()->getId())
            );
            $select->join(array(
                    'cat_index' => $resource->getMainTable()),
                implode(' AND ', $conditions),
                array()
            )->distinct();

            // Fix for Magento EE: Unknown column 'cat_index.category_id' in 'on clause'
            // This error is due to join clauses order, so we push permission clause to the end
            $fromPart = $select->getPart(Zend_Db_Select::FROM);
            if (isset($fromPart['permission_index_product'])) {
                $part = $fromPart['permission_index_product'];
                unset($fromPart['permission_index_product']);
                $fromPart['permission_index_product'] = $part;
                $select->setPart(Zend_Db_Select::FROM, $fromPart);
            }
        }
    }
}