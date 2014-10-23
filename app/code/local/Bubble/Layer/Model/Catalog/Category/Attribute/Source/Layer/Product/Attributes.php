<?php
/**
 * Define which product attributes can be displayed on category level.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Model_Catalog_Category_Attribute_Source_Layer_Product_Attributes
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected function _getAttributes()
    {
        $setIds = Mage::getModel('catalog/product')->getCollection()->getSetIds();
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setAttributeSetFilter($setIds)
            ->addIsFilterableFilter()
            ->setOrder('frontend_label', 'ASC');

        return $collection;
    }


    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $attributes = $this->_getAttributes();
            foreach ($attributes as $attribute) {
                $this->_options[] = array(
                    'label' => Mage::helper('catalog')->__($attribute['frontend_label']),
                    'value' => $attribute['attribute_code']
                );
            }
        }

        return $this->_options;
    }
}