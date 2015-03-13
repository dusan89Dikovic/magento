<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('followupemail/rule');
    }

    public function setGeneratesCouponFilter($hasCoupon = true)
    {
        $this->getSelect()->where('coupon_enabled', intval($hasCoupon));
        return $this;
    }

    public function getSelectCountSql() // Covers original bug in Varien_Data_Collection_Db 
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::HAVING);

        $countSelect->from('', 'COUNT(*)');
        return $countSelect;
    }
}