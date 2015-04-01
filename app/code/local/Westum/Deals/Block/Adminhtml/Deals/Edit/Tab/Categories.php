<?php
 
class Westum_Deals_Block_Adminhtml_Deals_Edit_Tab_Categories extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>$this->__('Deal settings')));
        $form->setHtmlIdPrefix('day_deal_');
        
        $dayDeal = Mage::registry('westum_deal_registry');
        
        
        
        
        $form->setValues($dayDeal->getData());
        $this->setForm($form);
        return parent::_prepareForm();
        
    }
}