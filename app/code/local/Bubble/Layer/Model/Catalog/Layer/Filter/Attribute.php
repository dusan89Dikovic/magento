<?php
/**
 * Layer filter attribute rewrite.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * @var string
     */
    protected $_optionsSeparator;

    /**
     * @var array
     */
    protected $_appliedOptionIds = array();

    /**
     * Define options separator
     */
    public function __construct()
    {
        parent::__construct();
        $this->_optionsSeparator = Bubble_Layer_Helper_Data::OPTIONS_SEPARATOR;
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @param Varien_Object $filterBlock
     * @return Bubble_Layer_Model_Catalog_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $param = $request->getParam($this->getRequestVar());
        if (null !== $param && '' !== $param) {
            $filter = explode($this->_optionsSeparator, $param);
            $applyOptionIds = array();
            foreach ($filter as $optionId) {
                // Transform keywords to ids
                if ($this->_helper()->isSeoEnabled()) {
                    $attrCode = $this->getAttributeModel()->getAttributeCode();
                    $optionId = $this->_helper()->getOptionId($attrCode, $optionId);
                }
                $text = $this->_getOptionText($optionId);
                if (null !== $optionId && strlen($text)) {
                    $applyOptionIds[] = $optionId;
                    $item = $this->_createItem($text, $optionId);
                    $this->getLayer()->getState()->addFilter($item);
                }
            }

            $this->_appliedOptionIds = $applyOptionIds;

            if (!empty($applyOptionIds)) {
                $this->_getResource()->applyFilterToCollection($this, $applyOptionIds);
            }
        }

        return $this;
    }

    /**
     * @return array|null
     */
    protected function _getItemsData()
    {
        if (!$this->_helper()->canShowFilter($this)) {
            return array();
        }

        $attribute = $this->getAttributeModel();
        $this->_requestVar = $this->getRequestVar();

        $key = $this->getLayer()->getStateKey().'_'.$this->getRequestVar();
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $this->_getResource()->getCount($this);
            $data = array();
            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($optionsCount[$option['value']]) ||
                            in_array($option['value'], $this->_appliedOptionIds))
                        {
                            $data[] = array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                            );
                        }
                    }
                    else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                        );
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getRequestVar()
    {
        return $this->_helper()->getAttributeRequestVar($this->getAttributeCode(), $this->getStoreId());
    }

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->getAttributeModel()->getAttributeCode();
    }

    /**
     * @return Bubble_Layer_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('bubble_layer');
    }
}