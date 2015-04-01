<?php

class Westum_Deals_Helper_Config extends Mage_Core_Helper_Abstract {
    
    const URL_REWRITES = 'westum_deals/configuration/url_rewrites';
    
    const APP_ENABLED = 'westum_deals/configuration/enable';
    
    const APP_DISABLED_ADVANCED = 'advanced/modules_disable_output/Westum_Deals';
    
    const ADD_TO_CART = 'westum_deals/configuration/add_to';
    
    const PRODUCT_PRICE = 'westum_deals/configuration/product_price';
    
    const DISPLAY_FUTURE = 'westum_deals/configuration/display_future';
    
    const COUNTER = 'westum_deals/configuration/livecounter';    
    
    
    public static function getConfig( $path, $storeId = null ) {        
        
        if(!$storeId) {
            
            $storeId = Mage::app()->getStore()->getId();
        }
        
        return Mage::getStoreConfig( $path );        
        
    }
    
    public static function shouldRenderApp() {
        
        return self::getConfig(self::APP_ENABLED) && !self::getConfig(self::APP_DISABLED_ADVANCED);
        
        
    }
   
}