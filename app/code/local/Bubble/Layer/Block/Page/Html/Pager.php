<?php
/**
 * Pager block rewrite.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Block_Page_Html_Pager extends Mage_Page_Block_Html_Pager
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