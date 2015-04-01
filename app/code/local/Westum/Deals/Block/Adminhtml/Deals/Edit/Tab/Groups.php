<?php
 
class Westum_Deals_Block_Adminhtml_Deals_Edit_Tab_Groups extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
		$form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>$this->__('Deal settings')));
        $form->setHtmlIdPrefix('day_deal_');
        
        $dayDeal = Mage::registry('westum_deal_registry');
       
        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('catalogrule')->__('NOT LOGGED IN')));
        }

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name'      => 'customer_group_ids[]',
            'label'     => $this->__('Disable deal for specific customer groups'),
            'title'     => $this->__('Disable deal for specific customer groups'),
            'required'  => false,
            'values'    => $customerGroups,
        ));
        
                  
        $form->setValues($dayDeal->getData());         
        $this->setForm($form);
        return parent::_prepareForm();
      
    }
}
