<?php

class Westum_Deals_Block_Adminhtml_Deals_Orders_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('magalterOrdersGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass() {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection() {

        $dayDeal = Mage::registry('westum_deal_registry');
        
        if(is_null($dayDeal)) {            
            $id = Mage::app()->getRequest()->getParam('id');
            $dayDeal = Mage::getModel('westum_deals/deal')->load($id);
        }

        if ($dayDeal->getId() && $dayDeal->getProductId()) {

            $collection = Mage::getResourceModel('sales/order_grid_collection');
            $instance = Mage::getModel('westum_deals/deal')->load($dayDeal->getId());

            $connection = Mage::getSingleton('core/resource')->getConnection('core/read');

            $select = $connection->select()->from(array('sales_item' => $collection->getTable('sales/order_item')), array('order_id'))
                    ->where('sales_item.product_id = ?', $dayDeal->getProductId());

            if ($instance->getAvailableFrom()) {
                $select->where('sales_item.created_at > ?', $instance->getAvailableFrom());
            }
            if ($instance->getAvailableTo()) {
                $select->where('sales_item.created_at < ?', $instance->getAvailableTo());
            }

            $orders = $connection->fetchCol($select);

            if (!empty($orders)) {

                $collection->getSelect()->where('entity_id IN (?)', $orders);
            }
        } else {

            $collection = new Varien_Data_Collection();
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sales')->__('Purchased From (Store)'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action', array(
                'header' => Mage::helper('sales')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url' => array('base' => 'adminhtml/sales_order/view'),
                        'field' => 'order_id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        return $this;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/ordersGrid', array('_current' => true, 'id' => Mage::app()->getRequest()->getParam('id')));
    }
    
    public function getRowUrl($row) {
        
        return $this->getUrl('adminhtml/sales_order/view', array(                    
                    'order_id' => $row->getId())
        );
    }

}
