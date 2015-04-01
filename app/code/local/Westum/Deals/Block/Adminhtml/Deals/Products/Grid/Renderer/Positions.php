<?php

class Westum_Deals_Block_Adminhtml_Deals_Products_Grid_Renderer_Positions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select
{    
    
    public function render(Varien_Object $row) {  
         
        $rowPositions = explode(',',$row->getPositions());
        $positions = Westum_Deals_Model_Source_Positions::toOptionArray();
        
        $html = null;
        
        foreach($rowPositions as $position) {            
            if(array_key_exists($position,$positions)) {
                $html .= $positions[$position]['label'] . '<br />';
            }
            
        }        
        
        return $html;
     
    }
 
}
