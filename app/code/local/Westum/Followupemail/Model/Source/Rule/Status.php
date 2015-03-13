<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Source_Rule_Status
{
    const RULE_STATUS_DISABLED = 0;
    const RULE_STATUS_ENABLED = 1;

    public static function toOptionArray()
    {
        return array(
            self::RULE_STATUS_ENABLED  => Mage::helper('followupemail')->__('Enabled'),
            self::RULE_STATUS_DISABLED => Mage::helper('followupemail')->__('Disabled'),
        );
    }
}