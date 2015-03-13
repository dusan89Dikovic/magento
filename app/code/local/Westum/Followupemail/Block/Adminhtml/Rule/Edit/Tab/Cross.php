<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Cross extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('cross', array('legend' => $this->__('Cross-sells')));

        $fieldset->addField(
            'cross_active', 'select',
            array(
                'label' => $this->__('Include cross-sells in email'),
                'name' => 'cross_active',
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            )
        );

        $fieldset->addField(
            'cross_source', 'select',
            array(
                'label' => $this->__('Cross-sells source'),
                'name' => 'cross_source',
                'values' => Westum_Followupemail_Model_Source_Rule_Cross::toOptionArray(),
            )
        );

        if ($data = Mage::registry('followupemail_data')) {
            $form->setValues($data);
        }
        return parent::_prepareForm();
    }
}