<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Model_Source_Queue_Status
{
    const QUEUE_STATUS_READY = 'R';
    const QUEUE_STATUS_SENT = 'S';
    const QUEUE_STATUS_FAILED = 'F';
    const QUEUE_STATUS_CANCELLED = 'C';

    public static function toOptionArray()
    {
        $helper = Mage::helper('followupemail');
        return array(
            self::QUEUE_STATUS_READY     => $helper->__('Ready to go'),
            self::QUEUE_STATUS_SENT      => $helper->__('Sent'),
            self::QUEUE_STATUS_FAILED    => $helper->__('Failed'),
            self::QUEUE_STATUS_CANCELLED => $helper->__('Cancelled'),
        );
    }
}