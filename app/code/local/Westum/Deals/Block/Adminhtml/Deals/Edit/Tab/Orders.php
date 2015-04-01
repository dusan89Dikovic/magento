<?php
 
class Westum_Deals_Block_Adminhtml_Deals_Edit_Tab_Orders extends Mage_Adminhtml_Block_Widget_Form
{
     
    protected function _toHtml() {
     
            return  "<div>{$this->getLayout()->createBlock('westum_deals/adminhtml_deals_orders_grid')->toHtml()}</div>";
     
    }
    
}