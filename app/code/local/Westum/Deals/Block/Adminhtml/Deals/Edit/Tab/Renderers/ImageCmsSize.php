<?php
 
class Westum_Deals_Block_Adminhtml_Deals_Edit_Tab_Renderers_ImageCmsSize extends Mage_Adminhtml_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected function _construct() {
        
        $this->setTemplate('westum_deals/renderers/imagecmssize.phtml');
        
    }

    public function render(Varien_Data_Form_Element_Abstract $element) {
        
        $this->setElement($element);
        return $this->renderView();
        
    }
}
