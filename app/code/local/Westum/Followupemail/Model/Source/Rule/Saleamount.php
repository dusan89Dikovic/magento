<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Source_Rule_Saleamount
{
    const CONDITION_SEPARATOR = ' ';

    // const CONDITION_EQ  = '=';
    // const CONDITION_GT  = '>';
    // const CONDITION_EGT = '>=';
    // const CONDITION_LT  = '<';
    // const CONDITION_ELT = '<=';
    // const CONDITION_NE  = '<>';

    const CONDITION_EQ = 1;
    const CONDITION_GT = 2;
    const CONDITION_EGT = 3;
    const CONDITION_LT = 4;
    const CONDITION_ELT = 5;
    const CONDITION_NE = 6;


    public function toOptionArray($addSelectOption = false)
    {
        $helper = Mage::helper('followupemail');

        $res = array();

        if ($addSelectOption) {
            $res[0] = $helper->__('Doesn\'t matter');
        }

        $res = array_merge(
            $res, array(
                       self::CONDITION_EQ  => $helper->__('Equals to'),
                       self::CONDITION_GT  => $helper->__('Greater than'),
                       self::CONDITION_EGT => $helper->__('Equals or greater than'),
                       self::CONDITION_LT  => $helper->__('Less than'),
                       self::CONDITION_ELT => $helper->__('Equals or less than'),
                       self::CONDITION_NE  => $helper->__('Not equals to'),
                  )
        );

        return $res;
    }

    public static function getCondition($id = null)
    {
        $conditions = self::getConditions();
        if (is_null($id) || !array_key_exists($id, $conditions)) {
            return 0;
        } else {
            return $conditions[$id];
        }
    }

    public static function getConditions()
    {
        return array(
            self::CONDITION_EQ  => '=',
            self::CONDITION_GT  => '>',
            self::CONDITION_EGT => '>=',
            self::CONDITION_LT  => '<',
            self::CONDITION_ELT => '<=',
            self::CONDITION_NE  => '<>',
        );
    }
}