<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Source_Product_Types
{
    const PRODUCT_TYPE_ALL = 'all';

    public function toShortOptionArray()
    {
        $productTypesArray = Mage::getConfig()->getNode('global/catalog/product/type')->asArray();
        $productTypes = array();

        $productTypes[self::PRODUCT_TYPE_ALL] = Mage::helper('followupemail')->__('All types');

        foreach ($productTypesArray as $typeCode => $typeArray) {
            $productTypes[$typeCode] = trim(
                substr($typeArray['label'], 0, (false !== $p = strpos($typeArray['label'], ' Product')) ? $p : 999)
            );
        }

        return $productTypes;
    }

    public function toOptionArray()
    {
        foreach ($this->toShortOptionArray() as $k => $v) {
            $productTypes[] = array(
                'value' => $k,
                'label' => $v
            );
        }

        return $productTypes;
    }
}