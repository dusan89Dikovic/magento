<?php

class Westum_Deals_Helper_Data extends Mage_Core_Helper_Abstract {
    
    const MYSQL_ZEND_DATE_TIME_FROMAT = 'yyyy-MM-dd HH:mm:ss';
    
    const MYSQL_DATE_TIME_FROMAT = 'Y-m-d H:i:s';
    
    const APP_PREFIX = 'westum';
    
    public static function getCustomerGroup() {
        
        $customerSession = Mage::getSingleton('customer/session');
        
        if($customerSession->isLoggedIn()) {
            
            return $customerSession->getCustomer()->getGroupId();
        }
        
        return 0;
       
    }
    
    public static function strToArray($str) {
      
       if(!is_string($str)) { return array(); }
        
       $unique = array_unique(explode(',',$str));
       
       return $unique; 
        
    }
    
    public static function clearStr($str) {
        
        return implode(',',self::strToArray($str));
        
    }
    
    public static function getISOTimestamp($datetime) {
        
        $zendDate = new Zend_Date($datetime,Zend_Date::ISO_8601);
        return $zendDate->setTimezone('UTC')->getTimestamp();        
        
    }

   

}