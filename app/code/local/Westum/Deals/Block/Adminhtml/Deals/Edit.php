<?php

class Westum_Deals_Block_Adminhtml_Deals_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct() {
        
        parent::__construct();        
        /* Add path to form */
        $this->_blockGroup = 'westum_deals';
        $this->_controller = 'adminhtml_deals';
        $this->_mode = 'edit';
        /* Set container header text */
        $this->_headerText = 'Deal edit mode';        
        $this->_formScripts[] = "function saveAndProceed(link) {  editForm.submit(link.replace(/{{tab_id}}/ig,idJsTabs.activeTab.id)); }";
        
    }   
    
     protected function _prepareLayout()
    {   
         $this->_addButton('save_and_edit', array(
            'label'     => Mage::helper('adminhtml')->__('Save and continue edit'),
            'onclick'   => 'saveAndProceed(\''.$this->getSaveAndContinueUrl().'\')',
            'class'     => 'save',
        ), 1);
         
        
       return parent::_prepareLayout();
        
    }
    
    public function getSaveAndContinueUrl() {
         
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'tab' => '{{tab_id}}'
        ));
        
    }
    
}
