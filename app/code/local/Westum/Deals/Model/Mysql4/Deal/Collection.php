<?php

class Westum_Deals_Model_Mysql4_Deal_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    
    protected static $_duplicateDeals = array();    
    
    public function _construct()
    {
        $this->_init('westum_deals/deal');
    }
    
    public function joinStores() {
        
        $this->getSelect()->joinLeft(array('store_table'=>$this->getTable('westum_deals/deals_store')),'main_table.deal_id = store_table.deal_id',
                array('stores'=>new Zend_Db_Expr('GROUP_CONCAT(store_table.store_id)')));   
      
        return $this;
        
    }
    
    public function joinStore($storeId = 0) {
        
         $this->getSelect()->join(array('store_table'=>$this->getTable('westum_deals/deals_store')),'main_table.deal_id = store_table.deal_id',array('store_table.store_id'))
            ->where('store_table.store_id = ?', $storeId);   
        
         return $this;
    }
    
    public function joinProductName($storeId = false, $attributeName = 'name') {
        
        if($storeId === false) {            
            $storeId = Mage::app()->getStore()->getId();
        }
        
         $attribute = Mage::getModel('eav/entity')->setType('catalog_product')->getAttribute('name');

         $this->getSelect()
                ->joinLeft(array("at_name_default" => $attribute->getBackend()->getTable()), "at_name_default.entity_id = main_table.product_id AND at_name_default.attribute_id = {$attribute->getId()} AND at_name_default.store_id = 0", array('product_name' => new Zend_Db_Expr('IF(at_name_store.value_id > 0, at_name_store.value, at_name_default.value)')))
                ->joinLeft(array("at_name_store" => $attribute->getBackend()->getTable()), "at_name_store.entity_id = main_table.product_id AND at_name_store.attribute_id = {$attribute->getId()} AND at_name_store.store_id = {$storeId}", array());
        
        return $this;
    }
    
    public function joinProductAttribute($attributeName = 'name', $storeId = false) {
        
        if($storeId === false) {            
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $unique = rand();
        
         $attribute = Mage::getModel('eav/entity')->setType('catalog_product')->getAttribute($attributeName);
         
         $this->getSelect()
                ->joinLeft(array("attr_default_{$unique}" => $attribute->getBackend()->getTable()), "attr_default_{$unique}.entity_id = main_table.product_id AND attr_default_{$unique}.attribute_id = {$attribute->getId()} AND attr_default_{$unique}.store_id = 0", array('product_status' => new Zend_Db_Expr("IF(attr_store_{$unique}.value_id > 0, attr_store_{$unique}.value, attr_default_{$unique}.value)")))
                ->joinLeft(array("attr_store_{$unique}" => $attribute->getBackend()->getTable()), "attr_store_{$unique}.entity_id = main_table.product_id AND attr_store_{$unique}.attribute_id = {$attribute->getId()} AND attr_store_{$unique}.store_id = {$storeId}", array())
                //->where("IF(attr_store_{$unique}.value_id > 0, attr_store_{$unique}.value, attr_default_{$unique}.value) = 1");
                ->having("`product_status`= 1");
        
        return $this;
    }
    
    public function joinProductStock() {
       
         if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory') && Mage::helper('catalog')->isModuleOutputEnabled('Mage_CatalogInventory')) {
             
             $websiteId = Mage::app()->getWebsite()->getId();
             
             $this->getSelect()                
                     ->join(array('product_status'=>$this->getTable('cataloginventory/stock_status')),"main_table.product_id = product_status.product_id AND product_status.website_id = {$websiteId}",array('product_status'=>''))
                     ->where('product_status.stock_status = 1');
         }  
         
        return $this;
        
    }
    
    public function joinProductWebsite($websiteId = false) {
        
        if(false === $websiteId) {            
            $websiteId = Mage::app()->getWebsite()->getId();
        }
        
        $this->getSelect()->join(array("product_website"=>$this->getTable('catalog/product_website')),"main_table.product_id = product_website.product_id AND product_website.website_id = {$websiteId}",array());
        
         return $this;
    }
    
    public function orderByPriority() {
        
         $this->getSelect()->order('main_table.priority ASC');
        
         return $this;
    }
    
    public function getDuplicateDeals() {
        
        return self::$_duplicateDeals;
        
    }
    
    
    public function joinCategories() {
        
        $this->getSelect()->joinLeft(array('category_table'=>$this->getTable('westum_deals/deals_category')),'main_table.deal_id = category_table.deal_id',
                array('categories'=>new Zend_Db_Expr('GROUP_CONCAT(category_table.category_id)')));
        
        return $this;
        
    }
    
    public function joinCustomerGroups($exclude = null) {
        
        $this->getSelect()->joinLeft(array('group_table'=>$this->getTable('westum_deals/deals_group')),'main_table.deal_id = group_table.deal_id',
                array('groups'=>new Zend_Db_Expr('GROUP_CONCAT(group_table.group_id)')));
        
        if($exclude !== NULL) {            
            $this->getSelect()->having("`groups` IS NULL OR `groups` NOT REGEXP '(^|,){$exclude}(,|$)'");
        }
        
        return $this;
        
    }
    
    public function filterCustomerGroups($exclude = null) {
        
         if($exclude !== NULL) {            
            $this->getSelect()->where("`groups` IS NULL OR `groups` NOT REGEXP '(^|,){$exclude}(,|$)'");
        }
        
        return $this;
        
    }
    
    public function filterByPresentCategory($categoryId = null) {
        
        if(is_null($categoryId)) { return $this; }
        
        $this->getSelect()->where("if(categories IS NULL,true,FIND_IN_SET({$categoryId},categories))");
       
        return $this;       
     
    }
    
    public function getNoCategory() {
        
         $this->getSelect()->where("categories IS NULL");
         
         return $this;
        
    }
    
     public function addTimeLimitFilter() {

        if (!Westum_Deals_Helper_Config::getConfig(Westum_Deals_Helper_Config::DISPLAY_FUTURE)) {

            $this->getSelect()
                    ->where("if(main_table.available_to is null, true, main_table.available_to > UTC_TIMESTAMP()) AND if(main_table.available_from is null, true, main_table.available_from < UTC_TIMESTAMP())");
        } else {

            $this->getSelect()
                    ->where("if(main_table.available_to is null, true, main_table.available_to > UTC_TIMESTAMP())");
        }

        return $this;
    }
    
    public function getNewDeals() {
        
         $this->getSelect()
                    ->where("main_table.available_from < UTC_TIMESTAMP() AND main_table.available_from IS NOT NULL AND main_table.new_email = 0");
        
        return $this;
        
    }
    
    public function getClosedDeals() {
        
         $this->getSelect()
                    ->where("main_table.available_to < UTC_TIMESTAMP() AND main_table.available_to IS NOT NULL AND main_table.close_email = 0");
        
        return $this;
        
    }
    
    public function addStatusFilter() {
        
        $this->getSelect()->where('main_table.status = ?', 1);
        
        return $this;
        
        
    }
    
    public function regroupByProductIds($register = false) {
        
        $scope = array();  
        $scopeIds = array();
        
        foreach($this as $deal) {
            
            if(array_key_exists($deal->getProductId(),$scope)) {
             
                $this->removeItemByKey($scope[$deal->getProductId()]->getId());
                
            }
            
            $scope[$deal->getProductId()] = $deal;    
            $scopeIds[$deal->getProductId()] = $deal->getDealId();
            
        }
        
        if($register) {        
            $this->_registerInCustomerSession($scopeIds);
        }
        
        return $scope;
    }
    
    private function _registerInCustomerSession($scopeIds) {
        
        Mage::getSingleton('customer/session')->unsMaglaterDealsByProduct();
        Mage::getSingleton('customer/session')->setMagalterDealsByProduct($scopeIds);
        
    }
     
    public function addDealIdFilter($dealId) {
         
        $this->getSelect()->where('main_table.deal_id = ?', (int) $dealId);
        
        return $this;
      
    }
    
    public function joinPosition() {
        
         $this->getSelect()->joinLeft(array('position_table'=>$this->getTable('westum_deals/deals_position')),'main_table.deal_id = position_table.deal_id',
                array('position'=>new Zend_Db_Expr('GROUP_CONCAT(position_table.position_id)')));
        
        return $this;
    }
    
    public function groupSelect($field = 'main_table.deal_id') {
        
        $this->getSelect()->group($field);
        
        return $this;
    }
    
    public function includeOnly($positions) {
        
        if(empty($positions)) {            
            return $this;
        }
        
        $sql = null;
        
        foreach($positions as $position) {            
            $sql .= "FIND_IN_SET({$position},`main_table`.`positions`) OR ";            
        }
        
        $sql = preg_replace("#\sOR\s$#s","",$sql);
        
        $this->getSelect()->where($sql);
        
        return $this;        
        
    }
    
    
}
