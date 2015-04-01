<?php

class Westum_Deals_Block_Livecounter extends Mage_Core_Block_Template {
    
    public static $_timeLeftCache = array();
    
    public static $_timeToCache = array();
    
    protected function _construct() {
        
        $this->setTemplate('westum_deals/livecounter.phtml');
        
    }
    
    
    public function getTimeToEvent() {
         
        if($this->getDealModel()->isActive()) {
           
            return $this->getTimeLeft();
            
        }
        
        else if($this->getDealModel()->isFuture()) {
         
            return $this->getTimeBefore();
            
        }
         
        return null;        
        
    } 

     
    public function getTimeLeft() {

        if ($this->getAvailableTo() && !array_key_exists($this->getProductId(),self::$_timeLeftCache)) {

            $to = new Zend_Date($this->getAvailableTo(), Zend_Date::ISO_8601);
            $now = gmdate('U');
            $toStamp = $to->setTimezone('UTC')->getTimestamp();

            if ($toStamp > $now) {                
                self::$_timeLeftCache[$this->getProductId()] = $toStamp - $now;
            }
        }
        // suppress error return null
        return @self::$_timeLeftCache[$this->getProductId()];
    }
    
    public function renderView() {
        
        if($this->dealIsInfinate()) {            
            return null;            
        }
        
        return parent::renderView();
    }

    public function getTimeBefore() {
        
         if ($this->getAvailableFrom() && !array_key_exists($this->getProductId(),self::$_timeToCache)) {
 
            $from = new Zend_Date($this->getAvailableFrom(), Zend_Date::ISO_8601);
            $now = gmdate('U');

            $fromStamp = $from->setTimezone('UTC')->getTimestamp();

            if ($fromStamp > $now) {
                self::$_timeToCache[$this->getProductId()] = $fromStamp - $now;
            }
            
         }
        
        // suppress error return null
        return @self::$_timeToCache[$this->getProductId()];
    }
    
    public function getTimeToEventLabel() {
         
         return $this->getParent()->getTimeToEventLabel();
        
    }
    
    public function dealIsInfinate() {
        
        return $this->getParent()->dealIsInfinate();
        
    }
  
    public function getDesignPackage() {

        return $this->getData('design_package'); 
        
    }
  
}