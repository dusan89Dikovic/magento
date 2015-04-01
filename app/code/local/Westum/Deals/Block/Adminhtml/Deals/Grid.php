<?php

class Westum_Deals_Block_Adminhtml_Deals_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {

        parent::__construct();
        $this->setId('magalterDealsGrid');
        $this->setUseAjax(false);
        $this->setSaveParametersInSession(true);
    }

    protected function _toHtml() {

        if (Mage::app()->isSingleStoreMode()) {
            return parent::_toHtml();
        }

        return Mage::getBlockSingleton('adminhtml/store_switcher')->setUseConfirm(false)->renderView() . parent::_toHtml();
    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('westum_deals/deal')->getCollection()
                ->joinProductName($this->_getStore()->getId());
        
        if ($this->_getStore()->getId()) {
            $collection->joinStore($this->_getStore()->getId());
        }
        Mage::log($collection->getSelect()->__toString(), null, "collection");
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('deal_id', array(
            'header' => Mage::helper('westum_deals')->__('Deal ID'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'deal_id'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('westum_deals')->__('Deal Name'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'name'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('westum_deals')->__('Related Product Name'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'product_name',
            'filter_condition_callback' => array($this, 'filterByProductName')
        ));

        $this->addColumn('price', array(
            'header' => Mage::helper('westum_deals')->__('Price'),
            'align' => 'left',
            'width' => '50px',
            'type' => 'price',
            'index' => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('westum_deals')->__('Deal Status'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'status',
            'type' => 'options',
            'sortable' => false,
            'options' => Westum_Deals_Model_Source_Status::toFlatArray()
        ));

        $this->addColumn('priority', array(
            'header' => Mage::helper('westum_deals')->__('Deal Priority'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'priority',
            'type' => 'text'
        ));


        $this->addColumn('positions', array(
            'header' => Mage::helper('westum_deals')->__('Deal Position'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'positions',
            'type' => 'options',
            'sortable' => false,
            'options' => Westum_Deals_Model_Source_Positions::toFlatArray(),
            'renderer' => 'westum_deals/adminhtml_deals_products_grid_renderer_positions',
            'filter_condition_callback' => array($this, 'filterByPosition')
        ));

        $this->addColumn('available_from', array(
            'header' => $this->__('Available From'),
            'index' => 'available_from',
            'type' => 'datetime',
            'width' => '150px',
            'gmtoffset' => true,
            'default' => '--'
        ));

        $this->addColumn('available_to', array(
            'header' => $this->__('Available To'),
            'index' => 'available_to',
            'type' => 'datetime',
            'width' => '150px',
            'gmtoffset' => true,
            'default' => '--'
        ));

        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'width' => '150px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('Edit'),
                    'url' => array(
                        'base' => '*/*/edit',
                        'params' => array('store' => $this->_getStore()->getId())
                    ),
                    'field' => 'id'
                ),
                array(
                    'caption' => $this->__('Delete'),
                    'url' => array(
                        'base' => '*/*/delete',
                        'params' => array('store' => $this->_getStore()->getId())
                    ),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
        ));


        if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
            $this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
        }

        $this->addExportType('*/*/exportCsv', Mage::helper('westum_deals')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('westum_deals')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('deal_id');
        $this->getMassactionBlock()->setFormFieldName('westum_deals');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));

        $statuses = Westum_Deals_Model_Source_Status::toFlatArray();


        $this->getMassactionBlock()->addItem('status', array(
            'label' => $this->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'values' => $statuses
                )
            )
        ));


        $positions = Westum_Deals_Model_Source_Positions::toFlatArray();
        $this->getMassactionBlock()->addItem('position', array(
            'label' => $this->__('Change position'),
            'url' => $this->getUrl('*/*/massPosition', array('_current' => true)),
            'additional' => 'westum_deals/adminhtml_deals_products_grid_renderer_chooser'
        ));

        $this->getMassactionBlock()->addItem('price', array(
            'label' => $this->__('Change price'),
            'url' => $this->getUrl('*/*/massPrice', array('_current' => true)),
            'additional' => array(
                'pos' => array(
                    'name' => 'price',
                    'type' => 'text',
                    'class' => 'required-entry',
                    'label' => $this->__('Price')
                )
            )
        ));

        return $this;
    }

    protected function _getStore() {

        if (!$this->getData('store')) {

            $storeId = (int) $this->getRequest()->getParam('store', 0);
            $this->setData('store', Mage::app()->getStore($storeId));
        }

        return $this->getData('store');
    }

    protected function filterByPosition($collection, $column) {

        $val = $column->getFilter()->getValue();
       
        $cond = "FIND_IN_SET('$val', {$column->getIndex()})";

        $collection->getSelect()->where($cond);
        
    }
    
     protected function filterByProductName($collection, $column) {
          
        $val = $column->getFilter()->getValue();
         
        if (!$val) {
            return $this;
        }
        else {
            $cond = "IF(at_name_store.value_id > 0, at_name_store.value, at_name_default.value) LIKE '%{$val}%'";
        }

        $collection->getSelect()->where($cond);
        
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array(
                    'store' => $this->_getStore()->getId(),
                    'id' => $row->getId())
        );
    }

}
