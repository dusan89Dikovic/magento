<?php
/**
 * Layer Javascript block.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Block_Catalog_Layer_Js extends Mage_Core_Block_Template
{
    public function getAjaxEnabled()
    {
        return Mage::getStoreConfigFlag('bubble_layer/general/enable_ajax') ? 'true' : 'false';
    }

    public function getAutoScrollEnabled()
    {
        return Mage::getStoreConfigFlag('bubble_layer/general/enable_auto_scroll') ? 'true' : 'false';
    }

    public function getJsPriceFormat()
    {
        $format = Mage::app()->getLocale()->getJsPriceFormat();
        $format['requiredPrecision'] = 0;

        return $format;
    }
}