<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Source_Linktracking_Types
{
    const LINKTRACKING_TYPE_LINK_ONLY = 'link';
    const LINKTRACKING_TYPE_LINK_CART = 'link_cart';
    const LINKTRACKING_TYPE_LINK_CART_ORDER = 'link_order';

    public static function toOptionArray()
    {
        $helper = Mage::helper('followupemail');

        return array(
            self::LINKTRACKING_TYPE_LINK_ONLY       => $helper->__('All links'),
            self::LINKTRACKING_TYPE_LINK_CART       => $helper->__('Incomplete abandoned carts (order not placed)'),
            self::LINKTRACKING_TYPE_LINK_CART_ORDER => $helper->__('Complete abandoned carts (order placed)'),
        );
    }
}