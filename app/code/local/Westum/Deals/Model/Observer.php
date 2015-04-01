<?php

class Westum_Deals_Model_Observer extends Varien_Object {
    
   protected static $_dealsCollection = null;
   
   protected static $_productDeals = null;
   
   protected static $_isProductPage = null;
   
   protected static $_dealsCollNoCategs = null;
   
   private function _createBlockInLayout($observer) {

        $template = new Westum_Deals_Block_Deals();
        $template->setTemplate('westum_deals/deal.phtml');        
        $observer->getLayout()->createBlock($template,'magalter_deal_of_the_day_'.rand());

        return $template;
   }
   
   public function core_block_abstract_to_html_before($observer) {
       
       if(!Westum_Deals_Helper_Config::shouldRenderApp()) {           
           return $this;
       }
       
        if ($observer->getBlock() instanceof Mage_Catalog_Block_Product_Price) {
            
            $_product = $observer->getBlock()->getProduct();
            // deals inserted in standard positions 
            if($_product->hasDealIdentity()) {               
                if (array_key_exists($_product->getId(), self::$_productDeals)) {                   
                    // there is deal related to product
                  if($_product->getFinalPrice() > self::$_productDeals[$_product->getId()]->getData('price'))
                    $_product->setFinalPrice(self::$_productDeals[$_product->getId()]->getData('price'));
                }
            }
            // deals inserted as CMS blocks and layout updates
            else {  
                if (array_key_exists($_product->getId(), self::$_dealsCollNoCategs)) {
                    // it's a native product block and deal is in future, so not timer and discount for such product
                    if(!$_product->getMagalterCmsDealIdentity() && !$this->showTimerAtTheProductPage($_product)) {
                       return $this;
                    }               
                    // there is deal related to product
                   if($_product->getFinalPrice() > self::$_dealsCollNoCategs[$_product->getId()]->getData('price'))
                    $_product->setFinalPrice(self::$_dealsCollNoCategs[$_product->getId()]->getData('price'));
                }               
            }          
        }
    }
    
    public function core_block_abstract_to_html_after($observer) {
 
       if(!Westum_Deals_Helper_Config::shouldRenderApp()) {           
           return $this;
       }
 
      if($this->isProductPage()) {
        if ($observer->getBlock() instanceof Mage_Catalog_Block_Product_Price) {

            $_product = $observer->getBlock()->getProduct();
             
            if($_product->hasDealIdentity() || !$this->_productIsCurrent($_product)) { 
                return $this; 
            }
            
            // Native product discount should be applied even if deal itself is hidden forthe cateogory  
            if (array_key_exists($_product->getId(), self::$_dealsCollNoCategs)) {
                 // we should not display timer and apply discount at the product page for future deals   
                if(!$this->showTimerAtTheProductPage($_product)) {
                    return $this;
                }              
                           
                $counterTemplate = Mage::getBlockSingleton('westum_deals/livecounter')
                        ->setData(self::$_dealsCollNoCategs[$_product->getId()]->getData())
                        ->setParent(self::$_dealsCollNoCategs[$_product->getId()])
                        ->setDealModel(self::$_dealsCollNoCategs[$_product->getId()])
                        ->setUseWrapper(true);
                
                $transport = $observer->getTransport();
                
                $transport->setHtml($transport->getHtml() . $counterTemplate->renderView());
                
                $_product->setDealIdentity(true);
                
            }
        }
      }
    }
    
    public function showTimerAtTheProductPage($_product) {
        
        return Mage::getSingleton('westum_deals/deal')
                ->setData(self::$_dealsCollNoCategs[$_product->getId()]->getData())
                ->canBePurchased();
         
    }
     
     
    public function getDealsCollection() { 
       
       if(!Westum_Deals_Helper_Config::shouldRenderApp()) {           
           return new Varien_Data_Collection();
       }
      
       if(is_null(self::$_dealsCollection)) {
           
           self::$_dealsCollection = Mage::getModel('westum_deals/deal')->getCollection()
                ->joinStore(Mage::app()->getStore()->getId())
                ->filterCustomerGroups(Westum_Deals_Helper_Data::getCustomerGroup())
                ->addStatusFilter()
                ->addTimeLimitFilter()
                ->joinProductAttribute('status')
                ->joinProductStock()
                ->joinProductWebsite()
                ->orderByPriority();
          
           $collectionClone = clone self::$_dealsCollection;
           self::$_dealsCollNoCategs = $collectionClone->regroupByProductIds(true);
                 
            if($category = Mage::registry('current_category')) {                
                if($category instanceof Varien_Object) {                  
                    self::$_dealsCollection->filterByPresentCategory($category->getId());
                }
            }
            else {
                /* Choose only deals with category NULL */
                self::$_dealsCollection->getNoCategory();
            }
            
           self::$_productDeals = self::$_dealsCollection->regroupByProductIds(false);
          
       }       
       
       return self::$_dealsCollection;      
    
   }
   
   public function prepareCartPrice($event) {
        
       if(!Westum_Deals_Helper_Config::shouldRenderApp()) {           
           return $this;
       }
        
        $_product = $event->getProduct();
        $_validDeals = (array) Mage::getSingleton('customer/session')->getMagalterDealsByProduct(); 
        
       $dealId = null;
       // If deal is actually allowed
       if(array_key_exists($_product->getId(),$_validDeals)) {
           // get deal id related to it
           $dealId = $_validDeals[$_product->getId()];           
       }
        
        $dayDeal = Mage::getModel('westum_deals/deal')->load($dealId);
        
        if($dayDeal->canBePurchased()) {           
            if($_product->getFinalPrice() > $dayDeal->getPrice()) {
                $_product->setFinalPrice($dayDeal->getPrice());
            }            
        }
        
	}
    
   public function controller_action_layout_generate_blocks_after($observer) {
      
       if(!Westum_Deals_Helper_Config::shouldRenderApp()) {           
           return $this;
       } 
       $dealsCollection = $this->getDealsCollection();
        
        foreach($dealsCollection as $dayDeal) {
           
           $positions = Westum_Deals_Helper_Data::strToArray($dayDeal->getPositions());
          
            foreach($positions as $position) {
                
                 if($info = Westum_Deals_Model_Source_Positions::getTranslation($position)) {
                     $parent = $observer->getLayout()->getBlock($info['block']);                     
                     if($parent) {
                          $block = $this->_createBlockInLayout($observer)->setData($dayDeal->getData())->setDealModel($dayDeal);
                          $block->setTemplate($info['view']);                          
                          /* For some reason checkout cart block doesn't render elements inserted
                           * If we are on checkout add deal to content top or bottom
                           */
                          if($info['block'] == 'checkout.cart') {
                                if($this->_getFullActionPath() == 'checkout_cart_index') {
                                    if($observer->getLayout()->getBlock('content')) {
                                        $observer->getLayout()->getBlock('content')->insert($block,'',(bool) $info['position']);
                                        continue;
                                    }
                                }
                           }
                           /***************************************************************************/

                          $parent->insert($block,'',(bool) $info['position']);                          
                     }                                
                 }                 
            }          
        }       
   }
   
   public function isProductPage() { 
       
       if(self::$_isProductPage !== null) {           
           return self::$_isProductPage;
       }           
       
       self::$_isProductPage = $this->_isProductPage() && $this->_inRegistry();
       
       return self::$_isProductPage;       
   }
   
   private function _isProductPage() {
        
        $moduleName = Mage::app()->getRequest()->getModuleName();
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $actionName = Mage::app()->getRequest()->getActionName();
        $fullPath = "{$moduleName}_{$controllerName}_{$actionName}";

        return $fullPath == 'catalog_product_view';  
            
    }   
    
     private function _productIsCurrent($_product) {        
        if(is_object(Mage::registry('current_product'))) {           
            return Mage::registry('current_product')->getId() == $_product->getId();
        }
        return false;
    }
    
    private function _inRegistry() {        
        if(is_object(Mage::registry('current_product'))) {           
            return true;
        }
        return false;
    }
    
     private function _getFullActionPath() {

        $request = Mage::app()->getRequest();
        $module = $request->getRequestedRouteName();
        $controller = $request->getRequestedControllerName();
        $action = $request->getRequestedActionName();

        return "{$module}_{$controller}_{$action}";

    }
   
}