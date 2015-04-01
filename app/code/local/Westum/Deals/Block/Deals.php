<?php

class Westum_Deals_Block_Deals extends Mage_Core_Block_Template {

    protected $_timeLeft = null;
    protected $_timeBefore = null;
     
    public function prepareRenderData($id) {
        
         $deal = Mage::getModel('westum_deals/deal')->load($id);
            
         if($deal->getId()) {  
             if(!$this->getTemplate()) {
                    $this->setTemplate('westum_deals/deal.phtml');
             }  
             $this->setData($deal->getData())->setDealModel($deal)->setData('magalter_cms_id', true); //->setDealIdentity(true); 
         }       
    }
   
    protected function getPreparedProduct() {
         
        if (!$this->getData('prepared_product')) {

            $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())->addMinimalPrice()->addFinalPrice()->addTaxPercents()->addStoreFilter();
            $collection->getSelect()->where('e.entity_id = ?', $this->getProductId());
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
  
                $collection->load();
                /* Add url rewrite block */
                $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
                $storeId = Mage::app()->getStore()->getId();
                $eavAttribute = new Mage_Eav_Model_Entity_Attribute();
                $activeAttr = $eavAttribute->loadByCode('catalog_category', 'is_active');

                $select = $resource->select()->reset()
                        ->from(array('category_product' => $collection->getTable('catalog/category_product')))
                        ->where('category_product.product_id = ?', $this->getProductId())
                        ->join(array('category_index' => $collection->getTable('catalog/category_product_index')), "category_product.category_id = category_index.category_id AND category_index.store_id = {$storeId} AND category_index.product_id = {$this->getProductId()}", array())
                        ->join(array('category' => $collection->getTable('catalog/category')), "category_product.category_id = category.entity_id")
                        ->join(array('attrib' => $activeAttr->getBackend()->getTable()), "attrib.entity_id = category.entity_id AND attrib.attribute_id = {$eavAttribute->getId()} AND attrib.store_id = 0", array())
                        ->joinLeft(array('attrib_store' => $activeAttr->getBackend()->getTable()), "attrib_store.entity_id = category.entity_id AND attrib_store.attribute_id = {$eavAttribute->getId()} AND attrib.store_id = {$storeId}", array())
                        ->where("IF(attrib_store.value_id > 0,attrib_store.value,attrib.value) = 1")
                        ->order('category.level ASC');
               
                $collection->addUrlRewrite($resource->fetchOne($select));
                
                $itemCollection = $collection->getFirstItem();
                
                if(!$this->getData('magalter_cms_id')) {
                    $itemCollection->setDealIdentity(true);                    
                }
                //we mark this product as our
                $itemCollection->setMagalterCmsDealIdentity(true);
           
            $this->setData('prepared_product', $itemCollection);
        }
        /* End of url rewrite block */

        return $this->getData('prepared_product');
    }
     
    protected function _toHtml() {
        
       if($this->getMagalterCmsId()) {             
           $this->prepareRenderData($this->getMagalterCmsId());          
       }
        
       if(!$this->isValidDeal()) {
           return null;
       }
       
       return parent::_toHtml();
 
    }
 
    public function getLiveCounter() {
       
        return Mage::getBlockSingleton('westum_deals/livecounter')->setData($this->getData())->setParent($this)->setUseWrapper(false)->renderView();
        
    }
    
    public function dealIsInfinate() {
        
        return $this->getDealModel()->dealIsInfinate();
        
    }
    
    public function getTimeToEventLabel() {

         return $this->getDealModel()->getTimeToEventLabel();
    }
    
    public function isValidDeal() {
        
       if(!$this->getDealModel() || !Westum_Deals_Helper_Config::shouldRenderApp() || !$this->getDealModel()->canBeViewed()) { 
           return false;
       }  
      
       $validIds = Mage::getSingleton('customer/session')->getMagalterDealsByProduct();
       
       if(!is_array($validIds) || !in_array($this->getDealId(), $validIds)) { return false;  }
       
       $this->getDealModel()->prepareAdditionalSettings();           
       
       return true;        
    }

    public function getCategoryHelper() {

        return Mage::getBlockSingleton('catalog/product_list');
    }

    public function addToCartIsAllowed() {

        return Westum_Deals_Helper_Config::getConfig(Westum_Deals_Helper_Config::ADD_TO_CART) && $this->getDealModel()->isActive();
    }
    
    public function productPriceIsAllowed() {
        
       return Westum_Deals_Helper_Config::getConfig(Westum_Deals_Helper_Config::PRODUCT_PRICE);
        
    }
    
    public function timerIsAllowed() {        
        
        return Westum_Deals_Helper_Config::getConfig(Westum_Deals_Helper_Config::COUNTER);
        
    }
    
    public function getDesignPackage() {

        return $this->getData('design_package'); 
        
    }

    public function getDayDealName() {

        return!$this->getData('name') ? $this->getPreparedProduct()->getName() : $this->getData('name');
    }

    public function getImageLabel($product=null, $mediaAttributeCode='image') {
        if (is_null($product)) {
            $product = $this->getPreparedProduct();
        }

        $label = $product->getData($mediaAttributeCode . '_label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return $label;
    }
    
    public function getImgResize() {
         
        return $this->getDealModel()->getData('additional_settings_img_resize');
   
    }
    
    public function getImgResizeHeight() {
        
        if(!$imgHeight = $this->getDealModel()->getData('additional_settings_img_resize_height')) {            
            return $this->getImgResize();
        }
         
        return $imgHeight;   
    }
    
    public function getCmsResize() {
         
        return $this->getDealModel()->getData('additional_settings_cms_resize');
   
    }
    
    public function getCmsResizeHeight() {
        
        if(!$cmsHeight = $this->getDealModel()->getData('additional_settings_cms_resize')) {
            return $this->getCmsResize();            
        }
         
        return $cmsHeight;
   
    }
    
    public function processAdditionalStyles($id) {
        
        $cssNamespace = "magalter_deal_{$this->getDesignPackage()}";
        /* Now we simply add random identifier to all additional styles */         
        $data = preg_replace("#(\#".$cssNamespace.")#is", '$1_'.$id, $this->getDealModel()->getData('additional_settings_rewrite_styles'));
        
        /* Add important declaration to all styles */
        $data = preg_replace("#\s+;#is",";",$data);
        $data = preg_replace("#(?<!!important);#is","!important;",$data);
        return $data;
        
    }

}