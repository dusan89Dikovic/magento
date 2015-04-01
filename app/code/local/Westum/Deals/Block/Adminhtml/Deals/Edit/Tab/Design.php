<?php
 
class Westum_Deals_Block_Adminhtml_Deals_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
		$form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>$this->__('Deal settings')));
        $form->setHtmlIdPrefix('day_deal_');
         
        $dayDeal = Mage::registry('westum_deal_registry');
        
        $designPackages = Westum_Deals_Model_Source_Design::toOptionArray();
         
        $fieldset->addField('magalter_design_package', 'select', array(
            'name'      => 'magalter_design_package[]',
            'label'     => $this->__('Choose design package'),
            'title'     => $this->__('Choose design package'),
            'required'  => false,
            'values'    => $designPackages,
        ));
        
        $fieldset->addField('additional_settings_rewrite_styles', 'textarea', array(
            'name'      => 'additional_settings_rewrite_styles',
            'label'     => $this->__('Apply additional styles to package'),
            'title'     => $this->__('Apply additional styles to package'),
            'required'  => false            
        ));
      
        /*
        $fieldset->addField('additional_settings_img_resize', 'text', array(
            'name'      => 'additional_settings_img_resize',
            'label'     => $this->__('Product image size, px'),
            'title'     => $this->__('Product image size, px'),
            'required'  => true            
        ));*/
        
          
        $fieldset->addField('additional_settings_img_resize_renderer', 'label', array(                
                'label' => $this->__('Product image size, px'),
                'title' => $this->__('Product image size, px')
        ))->setRenderer($this->getLayout()->createBlock('westum_deals/adminhtml_deals_edit_tab_renderers_imageSize'))->setDeal($dayDeal);
        
        $fieldset->addField('additional_settings_img_cms_resize_renderer', 'label', array(               
                'label' => $this->__('Use special image size in CMS blocks, px'),
                'title' => $this->__('Use special image size in CMS blocks, px')
        ))->setRenderer($this->getLayout()->createBlock('westum_deals/adminhtml_deals_edit_tab_renderers_imageCmsSize'))->setDeal($dayDeal);
         
         
        /*
        $fieldset->addField('additional_settings_cms_resize', 'text', array(
            'name'      => 'additional_settings_cms_resize',
            'checked'   => $dayDeal->hasAdditionalSettingsCms() ? true : false,
            'label'     => $this->__('Use special image size in CMS blocks'),
            'title'     => $this->__('Use special image size in CMS blocks'),
            'required'  => true   
        ));*/
        
        /*
         $fieldset->addField('additional_settings_product_price', 'select', array(
            'label' => $this->__('Show product price'),
            'title' => $this->__('Show product price'),
            'name' => 'magalter_status',
            'options' => array(
                '1' => $this->__('Yes'),
                '0' => $this->__('No'),
            )           
        )); */
                  
        $form->setValues($dayDeal->getData());         
        $this->setForm($form);
        return parent::_prepareForm();
      
    }
}
