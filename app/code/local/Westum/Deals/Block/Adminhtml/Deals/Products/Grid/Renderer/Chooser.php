<?php

class Westum_Deals_Block_Adminhtml_Deals_Products_Grid_Renderer_Chooser extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Massaction
{    
    
    protected function _toHtml() {
        
        $ms = new Varien_Data_Form_Element_Multiselect();
        $ms->setName('magalter_positions');
        $ms->setValues(Westum_Deals_Model_Source_Positions::toOptionArray());
        $ms->setForm(new Varien_Object());
        $ms->setSize(3);
         
        return $ms->getElementHtml();
    
    }
     
    public function render(Varien_Object $row)
    {
        
        $checked = NULL;
        if(!Mage::registry('westum_deal_check')) { 
            $checked = "checked='checked'";
            Mage::register('westum_deal_check',true,true);
        }
        
        return "<input type = 'radio' name = 'magalter_deal_choooser' value = '{$row->getEntityId()}' {$checked}  />";
    }
 
}
