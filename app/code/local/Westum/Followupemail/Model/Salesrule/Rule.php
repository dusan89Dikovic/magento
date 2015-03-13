<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Salesrule_Rule
{
    public function toOptionArray()
    {
        $rulesCollection = Mage::getModel('salesrule/rule')->getResourceCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('coupon_type', Mage::helper('followupemail/coupon')->getFUECouponsCode());

        $result = array('' => 'Please, select a rule');
        foreach ($rulesCollection as $rule) {
            $result[$rule->getRuleId()] = $rule->getName();
        }

        return $result;
    }
}
