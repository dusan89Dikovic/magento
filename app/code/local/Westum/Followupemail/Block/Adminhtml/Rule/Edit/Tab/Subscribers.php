<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Subscribers extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $data = Mage::registry('followupemail_data');
        if (is_object($data)) {
            $data = $data->getData();
        }

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('subscribers_only', array('legend' => $this->__('Newsletter Subscribers')));

        # send_to_subscribers_only field
        $fieldset->addField(
            'send_to_subscribers_only', 'select',
            array(
                'label' => $this->__('Send only to newsletter subscribers'),
                'name' => 'send_to_subscribers_only',
                'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            )
        );

        $fieldset = $form->addFieldset(
            'advanced_newsletters', array('legend' => $this->__('Advanced newsletter'))
        );

        if (Mage::helper('followupemail')->canUseAN()) {
            # anl_segments field
            $fieldset->addField(
                'anl_segments', 'multiselect',
                array(
                    'label' => $this->__('Send only to subscribers of segments'),
                    'name' => 'anl_segments[]',
                    'values' => Mage::getResourceModel('followupemail/rule')->getAdvancedNewsletterSegmentList()
                )
            );
        } else {
            $fieldset->addField(
                'anl_segments', 'multiselect',
                array(
                    'label' => $this->__('Send only to subscribers of segments'),
                    'name' => 'anl_segments[]',
                    'after_element_html' =>
                        '<a href="http://ecommerce.aheadworks.com/extensions/advanced-newsletter.html">'
                        . 'Advanced Newsletter extension</a> since version '
                        . Mage::helper('followupemail')->getMinANVersion()
                        . ' is required for targeted newsletters functionality',
                )
            );
        }

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}