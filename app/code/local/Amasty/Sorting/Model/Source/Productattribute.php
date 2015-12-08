<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */

class Amasty_Sorting_Model_Source_Productattribute
{
    public function toOptionArray()
    {
        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
            ->getAttributeCollection()
            ->addSetInfo();

        $options = array();
        foreach($attributes as $item) {
            if($item->getBackendType() == 'decimal') {
                $options[] = array(
                    'value' => Mage::helper('amsorting')->__($item->getAttributeCode()),
                    'label' => Mage::helper('amsorting')->__($item->getFrontendLabel()),
                );
            }
        }
        return $options;
    }
}