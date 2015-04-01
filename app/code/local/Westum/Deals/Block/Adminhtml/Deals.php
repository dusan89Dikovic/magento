<?php

class Westum_Deals_Block_Adminhtml_Deals extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {  
        
        parent::__construct(); 
        
        $this->_controller = 'adminhtml_deals';
        $this->_blockGroup = 'westum_deals';        
        $this->_headerText = $this->__('Current deals');        
         
    }

}