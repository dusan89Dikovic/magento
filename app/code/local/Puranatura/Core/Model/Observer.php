<?php

class Puranatura_Core_Model_Observer
{    
    public function catalog_product_view_predispatch($observer)
    {       
        $controller = $observer->getControllerAction();
        $productId = $controller->getRequest()->getParam('id');
        $helper = Mage::helper('puranatura_core');
        //var_dump($controller->getRequest()->getParams());
        //$helper->setReferralData($productId);
        Mage::getSingleton('customer/session')->setCountProductPagesViews('ddd');
        var_dump(Mage::getSingleton('customer/session')->getCountProductPagesViews());
        var_dump($helper->getReferralData($productId));
        die();
        if($productId){
        	
        	$product = Mage::getModel('catalog/product')->load($productId);
        }
    }

}