<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('followupemail/rule', 'id');
    }

    public function getRuleIdsByEventType($eventType)
    {
        $db = $this->_getReadAdapter();
        $now = Mage::getModel('core/date')->gmtDate();
        $select = $db->select()
            ->from($this->getMainTable(), 'id')
            ->where('event_type=?', $eventType)
            ->where('is_active=?', Westum_Followupemail_Model_Source_Rule_Status::RULE_STATUS_ENABLED)
            ->where('(active_to is null or active_to>=?)', $now)
            ->where('(active_from is null or active_from<=?)', $now);
        return $res = $db->fetchCol($select);
    }

    public function getTemplateContent(
        $modelName, $templateName,
        $fieldNames = array(
            'subject'         => 'template_subject',
            'content'         => 'template_text',
            'sender_name'     => 'template_sender_name',
            'sender_email'    => 'template_sender_email',
            'template_styles' => 'template_styles'
        )
    )
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from($this->getTable($modelName), $fieldNames)
            ->where('template_id=?', $templateName)
            ->orwhere('template_code=?', $templateName)
            ->limit(1);

        return $db->fetchRow($select);
    }

    public function isOrderStatusProcessed($orderId, $ruleId)
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from($this->getTable('followupemail/queue'), 'id')
            ->where('object_id=?', $orderId)
            ->where('rule_id=?', $ruleId)
            ->limit(1);

        return $db->fetchOne($select);
    }


    const ADVANCED_NEWSLETTER_SEGMENTS_ALL = 'ALL_SEGMENTS';

    /**
     * Getting segments list from AN
     *
     * @return array
     */
    public function getAdvancedNewsletterSegmentList()
    {
        if (!Mage::helper('followupemail')->canUseAN()) {
            return array();
        }
        $segments = Mage::getModel('advancednewsletter/api')->getSegmentsCollection();
        $_segments = array();
        foreach ($segments as $segment) {
            $_segments[] = array(
                'value' => $segment->getCode(),
                'label' => $segment->getTitle()
            );
        }
        return $_segments;
    }
}
