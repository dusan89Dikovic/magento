<?php
/**
 * Product list toolbar block rewrite.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    /**
     * @param array $params
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        return Mage::helper('bubble_layer')->buildUrl($params);
    }
}