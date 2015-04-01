<?php
 
class Westum_Deals_Block_Adminhtml_Deals_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('id');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Deal Of The Day'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general_section', array(
            'label'     => $this->__('Deal Dashboard'),
            'title'     => $this->__('Deal Dashboard'),
            'content'   => $this->getLayout()->createBlock('westum_deals/adminhtml_deals_edit_tab_dashboard')->toHtml(),
            'active'    => true
        ));
        
        $this->addTab('customer_groups', array(
            'label'     => $this->__('Customer Groups'),
            'title'     => $this->__('Customer Groups'),
            'content'   => $this->getLayout()->createBlock('westum_deals/adminhtml_deals_edit_tab_groups')->toHtml(),
            'active'    => false
        ));
        
        $this->addTab('categories', array(
            'label'     => $this->__('Categories Visibility'),
            'title'     => $this->__('Categories Visibility'),
            'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
            'class'     => 'ajax'
        ));
        
        $this->addTab('orders', array(
            'label'     => $this->__('Orders'),
            'title'     => $this->__('Orders'),
            'content'   => $this->getLayout()->createBlock('westum_deals/adminhtml_deals_edit_tab_orders')->toHtml(),
            'active'    => false
        )); 
        
        $this->addTab('design', array(
            'label'     => $this->__('Design'),
            'title'     => $this->__('Design'),
            'content'   => $this->getLayout()->createBlock('westum_deals/adminhtml_deals_edit_tab_design')->toHtml(),
            'active'    => false
        )); 
        
        $this->setActiveTab(preg_replace("/id_/i","",$this->getRequest()->getParam('tab')));

        return parent::_beforeToHtml();
    }

}
