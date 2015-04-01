<?php

class Westum_Deals_Block_Adminhtml_Deals_Products_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    
    
    protected function _prepareColumns() {
        
         $this->addColumn('magalter_chooser', array(
            'type' => 'radio',
            'index' => 'entity_id',
            'class' => 'radio',
            'html_name' => 'magalter_product_id',
            'align' => 'center',    
            'sortable' => false,            
            //'header' => $this->__('Choose related product'),     
           // 'renderer' => 'magalter_deals/adminhtml_deals_products_grid_renderer_chooser',
            'filter_condition_callback' => array($this, '_filterChooser')
        ));
         
        parent::_prepareColumns();  
        
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'=>'adminhtml/catalog_product/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));
        
        $this->removeColumn('set_name');
        $this->removeColumn('visibility');
        $this->removeColumn('websites');
       
    }
    
    protected function _filterChooser() {        
        func_get_args();
        return $this;
    }
    
    protected function _prepareGrid() {
        
        parent::_prepareGrid();
        
    }
    
     protected function _prepareMassaction() {
         
        return $this;
        
    }
    
    public function getRowUrl($row) {
        
       return $this;
        
    }
    
    public function removeColumn($columnId)
    {
        if (isset($this->_columns[$columnId])) {
            unset($this->_columns[$columnId]);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = key($this->_columns);
            }
        }
        return $this;
    }
 
}
