<?php

class Westum_Deals_Block_Adminhtml_Deals_Edit_Tab_Dashboard extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $this->__('Deal settings')));
        $form->setHtmlIdPrefix('day_deal_');
   
        $dayDeal = Mage::registry('westum_deal_registry');
        
        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        if ($dayDeal->getAvailableFrom()) {
            $dayDeal->setAvailableFrom(
                    Mage::app()->getLocale()->date($dayDeal->getAvailableFrom(), Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }
        if ($dayDeal->getAvailableTo()) {
            $dayDeal->setAvailableTo(
                    Mage::app()->getLocale()->date($dayDeal->getAvailableTo(), Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'magalter_name',
            'label' => $this->__('Deal Name'),
            'title' => $this->__('Deal Name'),
        ));
        
         /* Link to product edit mode page */
        if ($dayDeal->getProductId()) {
            $productLinkRenderer = $this->getLayout()
                    ->createBlock('westum_deals/adminhtml_deals_edit_tab_renderers_productLink');
            /* Get related product info */
            $relatedProduct = Mage::getModel('catalog/product')->load($dayDeal->getProductId());

            $fieldset->addField('related_product', 'label', array(
                'name' => 'magalter_related_product',
                'label' => $this->__('Related Product'),
                'title' => $this->__('Related Product')
            ))->setRenderer($productLinkRenderer)->setProduct($relatedProduct);
        }
 
        $fieldset->addField('price', 'text', array(
            'name' => 'magalter_price',
            'required' => true,
            'class'    => 'validate-zero-or-greater',
            'label' => $this->__('Price'),
            'title' => $this->__('Price'),
            'note'  => $this->__('Deal price will not be applied if native magento price is lower than deal price')
        ));
 
        $fieldset->addField('description', 'textarea', array(
            'name' => 'magalter_description',
            'label' => $this->__('Description'),
            'title' => $this->__('Description'),
        ));

        $fieldset->addField('status', 'select', array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'name' => 'magalter_status',
            'options' => array(
                '1' => $this->__('Enabled'),
                '0' => $this->__('Disabled'),
            ),
        ));
        
        
         $fieldset->addField('priority', 'text', array(
            'name' => 'magalter_priority',
            'label' => $this->__('Priority'),
            'title' => $this->__('Priority'),
            'after_element_html' => '<p class = "note" style = "width:274px;">' . $this->__('If there are more than one deal related to the same product, deal with greater priority will be visible (only integers are allowed)') . '</p>'
        ));
        
       // var_dump(Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)); die;

        $fieldset->addField('position', 'multiselect', array(
            'label' => $this->__('Position'),
            'title' => $this->__('Position'),
            'name' => 'magalter_position[]',
            'required' => true,
            'values' => Westum_Deals_Model_Source_Positions::toOptionArray()
        ));

        $fieldset->addField('available_from', 'date', array(
            'name' => 'magalter_available_from',
            'label' => $this->__('Available From'),
            'title' => $this->__('Available From'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => $outputFormat,
            'time' => true
        ));

        $fieldset->addField('available_to', 'date', array(
            'name' => 'magalter_available_to',
            'label' => $this->__('Available To'),
            'title' => $this->__('Available To'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => $outputFormat,
            'time' => true
        ));

       
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'hidden', array(
                'name' => 'store_ids[]',
                'value' => Mage::app()->getStore()->getId(),
            ));
        } else {
            $fieldset->addField('store_ids', 'multiselect', array(
                'name' => 'store_ids[]',
                'label' => $this->__('Store View'),
                'title' => $this->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }

        /* Product grid */
        if(!$dayDeal->getProductId()) {             
            $productGridRenderer = $this->getLayout()
                    ->createBlock('westum_deals/adminhtml_deals_edit_tab_renderers_productGrid');


            $fieldset->addField('related_product_grid', 'label', array(
                'name' => 'related_product_grid',
                'label' => $this->__('Related Product'),
                'title' => $this->__('Related Product')
            ))->setRenderer($productGridRenderer);
       }
      
       if( Mage::getSingleton('adminhtml/session')->getElementData() ) {          
            $form->setValues(Mage::getSingleton('adminhtml/session')->getElementData());
            Mage::getSingleton('adminhtml/session')->setElementData(null);
        } elseif( $dayDeal->getId() ) {            
            $form->setValues($dayDeal->getData());          
        }
 
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
