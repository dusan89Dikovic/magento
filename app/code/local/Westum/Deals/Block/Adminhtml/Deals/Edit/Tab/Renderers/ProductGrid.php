<?php
 
class Westum_Deals_Block_Adminhtml_Deals_Edit_Tab_Renderers_ProductGrid extends Mage_Adminhtml_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected function _construct() {
        
        $this->setTemplate('westum_deals/renderers/productgrid.phtml');
        
    }

    public function render(Varien_Data_Form_Element_Abstract $element) {
        
        $this->setElement($element);        
        $this->setGridHtml($this->getLayout()->createBlock('westum_deals/adminhtml_deals_products_grid')->toHtml());       
        return $this->renderView();
        
    }
}
