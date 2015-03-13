<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Details_Saleamount
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function __construct()
    {
        $this->setTemplate('followupemail/rule/edit/details/saleamount.phtml');
    }

    public function isMultiWebsites()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}