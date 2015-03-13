<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Sender extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $data = Mage::registry('followupemail_data');

        if (is_object($data)) $data = $data->getData();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('followupemail_sender_details', array('legend' => $this->__('Sender Details')));

        # sender_name field
        $fieldset->addField(
            'sender_name', 'text',
            array(
                'label' => $this->__('Sender name'),
                'name' => 'sender_name',
            )
        );

        # sender_email field
        $fieldset->addField(
            'sender_email', 'text',
            array(
            'label' => $this->__('Sender email'),
                'name' => 'sender_email',
                'after_element_html' =>
                '<span class="note"><small>'
                    . $this->__('Redefines sender for this rule. Sender from the general settings is used by default')
                    . '</small></span>',
            )
        );

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}