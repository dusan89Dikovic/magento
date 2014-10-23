<?php
/**
 * Layer helper.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Helper_Data extends Mage_Core_Helper_Abstract
{
    const OPTIONS_SEPARATOR         = ',';
    const ATTRIBUTE_LABELS_CACHE_ID = 'bubble_layer_attribute_labels';
    const OPTIONS_MAPPING_CACHE_ID  = 'bubble_layer_options_mapping';

    /**
     * Store options because unserializing a large number of options is slow
     *
     * @var array $_optionsMapping
     */
    protected $_optionsMapping;

    /**
     * @param array $query
     * @return mixed
     */
    public function buildUrl(array $query)
    {
        $params = array(
            '_current'      => true,
            '_use_rewrite'  => true,
            '_query'        => $query,
        );
        Mage::app()->setUseSessionVar(false);
        $url = Mage::getUrl('*/*/*', $params);
        // Unescape options separator
        $url = str_replace(urlencode(self::OPTIONS_SEPARATOR), self::OPTIONS_SEPARATOR, $url);

        return $url;
    }

    /**
     * @param Mage_Catalog_Model_Layer_Filter_Abstract $filter
     * @return bool
     */
    public function isFilterMultiple(Mage_Catalog_Model_Layer_Filter_Abstract $filter)
    {
        return
            $filter instanceof Mage_Catalog_Model_Layer_Filter_Category ||
            ($filter instanceof Mage_Catalog_Model_Layer_Filter_Attribute
                && !$filter->getAttributeModel()->getSource() instanceof Mage_Eav_Model_Entity_Attribute_Source_Boolean);
    }

    /**
     * @param Mage_Catalog_Model_Layer_Filter_Abstract $filter
     * @return bool
     */
    public function isAttributeFilter(Mage_Catalog_Model_Layer_Filter_Abstract $filter)
    {
        return $filter instanceof Mage_Catalog_Model_Layer_Filter_Attribute
            && !$filter->getAttributeModel()->getSource() instanceof Mage_Eav_Model_Entity_Attribute_Source_Boolean;
    }

    /**
     * @param Mage_Catalog_Model_Layer_Filter_Abstract $filter
     * @return bool
     */
    public function isCategoryFilter(Mage_Catalog_Model_Layer_Filter_Abstract $filter)
    {
        return $filter instanceof Mage_Catalog_Model_Layer_Filter_Category;
    }

    /**
     * @param Mage_Catalog_Model_Layer_Filter_Abstract $filter
     * @return bool
     */
    public function isAttributeFilterMultiple(Mage_Catalog_Model_Layer_Filter_Abstract $filter)
    {
        return $this->isFilterMultiple($filter) && $this->isAttributeFilter($filter);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isAjaxEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('bubble_layer/general/enable_ajax', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isAutoScrollEnabled($store = null)
    {
        return $this->isAjaxEnabled() && Mage::getStoreConfigFlag('bubble_layer/general/enable_auto_scroll', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isSeoEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('bubble_layer/general/enable_seo', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isPriceSliderEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('bubble_layer/general/enable_price_slider', $store);
    }

    /**
     * @param $attrCode
     * @param null $store
     * @return string
     */
    public function getAttributeRequestVar($attrCode, $store = null)
    {
        $var = $attrCode;
        if (!$this->isSeoEnabled()) {
            return $var;
        }
        $labels = $this->getAttributeLabels();
        $storeId = Mage::app()->getStore($store)->getId();
        if (isset($labels[$storeId][$attrCode])) {
            $var = $this->formatRequestVar($labels[$storeId][$attrCode]);
        }

        return $var;
    }

    /**
     * @param string $default
     * @return string
     */
    public function getCategoryRequestVar($default = 'cat')
    {
        if (!$this->isSeoEnabled()) {
            return $default;
        }
        $label = Mage::helper('catalog')->__('Category');

        return $this->formatRequestVar($label);
    }

    /**
     * @param string $default
     * @return string
     */
    public function getPriceRequestVar($default = 'price')
    {
        if (!$this->isSeoEnabled()) {
            return $default;
        }
        $label = Mage::helper('catalog')->__('Price');

        return $this->formatRequestVar($label);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return Mage::getSingleton('catalog/layer')->getState()->getFilters();
    }

    /**
     * @return array
     */
    public function getAttributeLabels()
    {
        $cacheId = self::ATTRIBUTE_LABELS_CACHE_ID;
        if (false !== ($data = Mage::app()->getCache()->load($cacheId))) {
            $labels = unserialize($data);
        } else {
            $resource       = Mage::getSingleton('core/resource');
            $connection     = $resource->getConnection('read');
            $attrTable      = $resource->getTableName('eav/attribute');
            $attrLabelTable = $resource->getTableName('eav/attribute_label');
            $labels         = array();
            foreach (Mage::app()->getStores(true) as $store) {
                // Fetching attribute labels for current store
                $select = $connection->select();
                $joinExpression = $connection
                    ->quoteInto('a.attribute_id = al.attribute_id AND al.store_id = ?', (int) $store->getId());
                $select->from(array('a' => $attrTable), 'attribute_code')
                    ->joinLeft(
                        array('al' => $attrLabelTable),
                        $joinExpression,
                        array('label' => $connection->getIfNullSql('al.value', 'a.frontend_label'))
                    )
                    ->where('a.entity_type_id = ?', Mage::getModel('catalog/product')->getResource()->getTypeId())
                    ->where('a.frontend_input IN(?)', array('select', 'multiselect'));

                $labels[$store->getId()] = $connection->fetchPairs($select);
            }

            Mage::app()->getCache()->save(serialize($labels), $cacheId);
        }

        return $labels;
    }

    /**
     * @return array
     */
    public function getOptionsMapping()
    {
        if (null !== $this->_optionsMapping) {
            return $this->_optionsMapping;
        }

        $cacheId = self::OPTIONS_MAPPING_CACHE_ID;
        if (false !== ($data = Mage::app()->getCache()->load($cacheId))) {
            $options = unserialize($data);
        } else {
            /** @var $resource Mage_Core_Model_Resource */
            $resource           = Mage::getSingleton('core/resource');
            $connection         = $resource->getConnection('read');
            $attrTable          = $resource->getTableName('eav/attribute');
            $optionTable        = $resource->getTableName('eav/attribute_option');
            $optionValueTable   = $resource->getTableName('eav/attribute_option_value');

            // Fetching all product attribute options
            $select = $connection->select();
            $select->from(array('o' => $optionTable), 'option_id')
                ->join(array('a' => $attrTable), 'o.attribute_id = a.attribute_id', 'attribute_code')
                ->join(array('v' => $optionValueTable), 'o.option_id = v.option_id', array('store_id', 'value'))
                ->where('a.frontend_input IN(?)', array('select', 'multiselect'));
            $rows = $connection->fetchAll($select);

            // Array that contains options mapping
            $options = array(
                'ids' => array(),
                'keys' => array(),
            );

            // Filling values explicitly defined
            foreach ($rows as $row) {
                $storeId = $row['store_id'];
                $key = $this->formatKey($row['value']);
                $row['key'] = $key;
                $options['ids'][$storeId][$row['option_id']] = $row;
                $options['keys'][$storeId][$row['attribute_code']][$key] = $row;
            }

            // Filling empty values for stores that don't have a value defined
            foreach ($options['ids'][Mage_Core_Model_App::ADMIN_STORE_ID] as $row) {
                foreach (Mage::app()->getStores() as $store) {
                    $storeId = $store->getId();
                    if (!isset($options['ids'][$storeId][$row['option_id']])) {
                        $key = $this->formatKey($row['value']);
                        $row['key'] = $key;
                        $options['ids'][$storeId][$row['option_id']] = $row;
                        $options['keys'][$storeId][$row['attribute_code']][$key] = $row;
                    }
                }
            }

            Mage::app()->getCache()->save(serialize($options), $cacheId);
        }

        $this->_optionsMapping = $options;

        return $options;
    }

    /**
     * @param string $attrCode
     * @param string $optionKey
     * @param null $store
     * @return string
     */
    public function getOptionId($attrCode, $optionKey, $store = null)
    {
        if (!$optionKey) {
            return $optionKey;
        }
        $options = $this->getOptionsMapping();
        $storeId = Mage::app()->getStore($store)->getId();
        if (!isset($options['keys'][$storeId][$attrCode][$optionKey])) {
            return $optionKey;
        }
        $optionId = $options['keys'][$storeId][$attrCode][$optionKey]['option_id'];

        return $optionId;
    }

    /**
     * @param $categoryId
     * @return mixed
     */
    public function getCategoryKey($categoryId)
    {
        if (!$categoryId) {
            return $categoryId;
        }
        $key = $categoryId;
        $category = Mage::getModel('catalog/category')->load($categoryId);
        if ($category->getId()) {
            $key = $category->getUrlKey();
        }

        return $key;
    }

    /**
     * @param $key
     * @param $currentCategory
     * @param null $store
     * @return mixed
     */
    public function getCategoryId($key, $currentCategory, $store = null)
    {
        $category = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('parent_id', $currentCategory->getId())
            ->addAttributeToFilter('url_key', $key)
            ->setStoreId(Mage::app()->getStore($store)->getId())
            ->getFirstItem();

        return $category->getId() ? $category->getId() : $key;
    }

    /**
     * @param int $optionId
     * @param null $store
     * @return string
     */
    public function getOptionKey($optionId, $store = null)
    {
        if (!$optionId) {
            return $optionId;
        }
        $options = $this->getOptionsMapping();
        $storeId = Mage::app()->getStore($store)->getId();
        if (!isset($options['ids'][$storeId][$optionId])) {
            return $optionId;
        }
        $key = $options['ids'][$storeId][$optionId]['key'];

        return $key;
    }

    /**
     * @param $str
     * @return string
     */
    public function formatKey($str)
    {
        $str = Mage::helper('catalog/product_url')->format($str);
        $key = preg_replace('#[^0-9a-z]+#i', '-', $str);
        $key = strtolower($key);
        $key = trim($key, '-');

        return $key;
    }

    /**
     * @param $str
     * @return string
     */
    public function formatRequestVar($str)
    {
        $str = Mage::helper('catalog/product_url')->format($str);
        $key = preg_replace('#[^0-9a-z]+#i', '_', $str);
        $key = strtolower($key);
        $key = trim($key, '_');

        return $key;
    }

    /**
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return bool
     */
    public function canShowFilter($filter)
    {
        $attributes = Mage::getSingleton('catalog/layer')
            ->getCurrentCategory()
            ->getLayerProductAttributes();

        // Compatibility with Flat Catalog Category
        if (is_string($attributes) && strlen($attributes)) {
            $attributes = explode(',', $attributes);
        }

        $attrCode = false;
        if ($this->isAttributeFilter($filter)) {
            $attrCode = $filter->getAttributeModel()->getAttributeCode();
        } else if ($filter instanceof Mage_Catalog_Model_Layer_Filter_Price) {
            $attrCode = 'price';
        }

        return !is_array($attributes) || in_array($attrCode, $attributes);
    }
}