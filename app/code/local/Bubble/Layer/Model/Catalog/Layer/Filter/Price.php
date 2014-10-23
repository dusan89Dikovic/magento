<?php
/**
 * Layer filter price rewrite.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    /**
     * @return string
     */
    public function getRequestVar()
    {
        return $this->_helper()->getPriceRequestVar();
    }

    /**
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if (Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION) == self::RANGE_CALCULATION_IMPROVED) {
            return $this->_getCalculatedItemsData();
        } elseif ($this->getInterval() && !$this->_helper()->isPriceSliderEnabled()
            || !$this->_helper()->canShowFilter($this)
        ) {
            return array();
        }

        $range      = $this->getPriceRange();
        $dbRanges   = $this->getRangeItemCounts($range);
        $data       = array();

        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];

            foreach ($dbRanges as $index => $count) {
                $fromPrice = ($index == 1) ? '' : (($index - 1) * $range);
                $toPrice = ($index == $lastIndex) ? '' : ($index * $range);

                $data[] = array(
                    'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
                    'value' => $fromPrice . '-' . $toPrice,
                    'count' => $count,
                );
            }
        }

        return $data;
    }

    /**
     * @return Bubble_Layer_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('bubble_layer');
    }
}