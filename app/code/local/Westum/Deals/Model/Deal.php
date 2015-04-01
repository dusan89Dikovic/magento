<?php

class Westum_Deals_Model_Deal extends Mage_Core_Model_Abstract {

    protected function _construct() {
        parent::_construct();
        $this->_init('westum_deals/deal');
    }

    public function isActive() {

        $now = gmdate('U');

        $availFrom = Westum_Deals_Helper_Data::getISOTimestamp($this->getAvailableFrom());
        $availTo = Westum_Deals_Helper_Data::getISOTimestamp($this->getAvailableTo());

        if (($availFrom < $now || !$this->getAvailableFrom()) && ($availTo > $now || !$this->getAvailableTo())) {
            return true;
        }

        return false;
    }
    
    public function alreadyActive($request = array()) {
        
        if((!$request['status'] || !isset($request['store_ids'])) && !empty($request)) {
            return false;
        }   
       
        if($this->getId() && $this->getProductId()) {
            $productId = $this->getProductId();            
        }
        elseif(isset($request['magalter_product_id'])) {
            $productId = $request['magalter_product_id'];
        }
        else {
            return false;
        }
        
        $collection = $this->getCollection()->addStatusFilter()->joinStores()->addFieldToFilter('product_id', array('eq' => $productId));
        
        if($this->getId()) {            
            $collection->addFieldToFilter('main_table.deal_id', array('neq' => $this->getId()));
        }
        
        $where = 'FIND_IN_SET(0, stores)';
        
       if(!empty($request)) {
            if(in_array(0, $request['store_ids'])) {            
                $stores = Mage::app()->getStore()->getCollection()->getAllIds();
            }
            else {
                $stores = $request['store_ids'];
            }        
       }
       else {
            if(in_array(0, $this->getStoreIds())) {            
                $stores = Mage::app()->getStore()->getCollection()->getAllIds();
            }
            else {
                $stores = $this->getStoreIds();
            }           
       }
        
        foreach($stores as $store) {            
            $where.= " OR FIND_IN_SET({$store}, stores)";
        }
       
        $collection->getSelect()->having($where);
         
        $deals = $collection->getConnection()->fetchAll($collection->getSelect());  
        
        $ids = array();
        if(empty($deals)) {            
            return false;
        }        
        
        foreach($deals as $deal) {
            $ids[] = $deal['deal_id'];
        }
       
        return implode(",",$ids);
      
    }

    /**
     * Check if future deal
     * Don't check for current moment as its already filtered in collection
     * @return bool 
     */
    public function isFuture() {

        $availFrom = Westum_Deals_Helper_Data::getISOTimestamp($this->getAvailableFrom());
        $availTo = Westum_Deals_Helper_Data::getISOTimestamp($this->getAvailableTo());

        if ($this->getAvailableFrom() && ($availTo > $availFrom || !$this->getAvailableTo())) {
            return true;
        }

        return false;
    }

    public function isInfinate() {

        return $this->isActive() && !$this->getAvailableTo();
    }

    protected function _exists() {

        return (bool) $this->getId();
    }

    public function isEnabled() {

        if ($this->getStatus()) {
            return true;
        }
        return false;
    }
    
    public function isValidStore() {
        
        $stores = $this->getData('store_ids');
        
        if(!is_array($stores)) {
            $stores = $this->getData('store_id');
            if(!is_numeric($stores)) {
                return false;
            }
            $stores = (array) $stores;            
        }
       
        if(in_array(0, $stores)) {             
            return true;
        }
        if(in_array(Mage::app()->getStore()->getId(), $stores)) {
            return true;
        }        
        return false;        
    }

    public function isValidCustomerGroup() {

        $customerGroup = Westum_Deals_Helper_Data::getCustomerGroup();

        return!preg_match('/(^|,)' . $customerGroup . '($|,)/is', $this->getGroups());
    }

    public function canBePurchased() {

        return $this->_exists() && $this->isActive() && $this->isEnabled() && $this->isValidCustomerGroup() && $this->isValidStore() && $this->getProductId();
    }
    
    public function canBeViewed() {

        return $this->_exists() && 
                ($this->isActive() || ($this->isFuture() && Westum_Deals_Helper_Config::getConfig(Westum_Deals_Helper_Config::DISPLAY_FUTURE))) && 
                $this->isEnabled() && 
                $this->isValidCustomerGroup() && 
                $this->isValidStore() && 
                $this->getProductId();
        
    }

    protected function _afterLoad() {

        parent::_afterLoad();

        $this->setPosition(@explode(',', $this->getPositions()));
        $this->setCustomerGroupIds(@explode(',', $this->getGroups()));
        $this->setMagalterDesignPackage(@explode(',', $this->getDesignPackage()));
        $this->loadStoreIds();

        if (in_array(0, $this->getStoreIds())) {
            $this->setStoreIds(array(0));
        }
        
        $this->setPrice( sprintf("%.2f", $this->getPrice()) );

        $this->prepareAdditionalSettings();
        
        $this->setData('item_is_loaded', true);

        return $this;
    }

    public function prepareAdditionalSettings() {
        
        if($this->getData('item_is_loaded')) {
            return $this;
        }
       
        $additional = @unserialize($this->getAdditionalSettings());

        if (is_array($additional)) {
            $this->addData($additional);
        }

        if (false === $additional) {
            $this->setAdditionalSettingsImgResize(Westum_Deals_Model_Source_Design::IMAGE_RESIZE);
            $this->setAdditionalSettingsImgResizeHeight(Westum_Deals_Model_Source_Design::IMAGE_CMS_RESIZE_H);
            $this->setAdditionalSettingsCmsResize(Westum_Deals_Model_Source_Design::IMAGE_CMS);
            $this->setAdditionalSettingsCmsResizeHeight(Westum_Deals_Model_Source_Design::IMAGE_CMS_RESIZE_H);
            $this->setAdditionalSettingsProductPrice(true);
        }
    }

    protected function _beforeSave() {

        parent::_beforeSave();
        
        /* Data modified only for new deals */
        $categoryIds = $this->getCategoryIds();
        if ($categoryIds == '0' || $categoryIds == '') {
            $categoryIds = null;
        }

        if (is_array($this->getCustomerGroupIds())) {           
            $this->setGroups(implode(',', $this->getCustomerGroupIds()));
        } 
        else {
            $this->setGroups($this->getCustomerGroupIds());
        }
 
        if($this->getCategoryIds() !== null) {
            $this->setCategories($categoryIds ? Westum_Deals_Helper_Data::clearStr($categoryIds) : null);
        }

        if (is_array($this->getPosition())) {
            $this->setPositions(implode(',', $this->getPosition()));
        }
        if (is_array($this->getDesignPackage())) {
            $this->setDesignPackage(implode(',', $this->getDesignPackage()));
        }

        return $this;
    }
    
     public function getTimeToEventLabel() {

        if ($this->isInfinate()) { 
            return Mage::helper('westum_deals')->__('DEAL OF THE DAY');            
        } else if ($this->isActive()) {
            return Mage::helper('westum_deals')->__('TIME LEFT');
        } else if ($this->isFuture()) {
            return Mage::helper('westum_deals')->__('TIME TO EVENT');
        }

        return $this->__('DEAL OF THE DAY');
    }
    
     public function dealIsInfinate() {
          
        return $this->isInfinate();
        
    }

    public function loadStoreIds() {

        $this->_getResource()->loadStoreIds($this);
        return $this;
    }

}