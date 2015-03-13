<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Source_Rule_Cross
{
    const MAGENTO_CROSS = 'magento_cross';
    const MAGENTO_RELATED = 'magento_related';
    const MAGENTO_UPSELLS = 'magento_upsells';
    const Westum_WBTAB = 'Westum_wbtab';
    const Westum_ARP2 = 'Westum_arp2';

    public static function toOptionArray()
    {
        $helper = Mage::helper('followupemail');
        $options = array(
            array('value' => self::MAGENTO_CROSS, 'label' => $helper->__('Magento Cross-sell products')),
            array('value' => self::MAGENTO_RELATED, 'label' => $helper->__('Magento Related products')),
            array('value' => self::MAGENTO_UPSELLS, 'label' => $helper->__('Magento Upsell products')),
            array('value' => self::Westum_WBTAB, 'label' => $helper->__('AW Who bought this also bought')),
            array('value' => self::Westum_ARP2, 'label' => $helper->__('AW Autorelated products 2')),
        );
        return $options;
    }

}