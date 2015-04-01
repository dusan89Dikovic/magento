<?php

class Westum_Deals_Model_Cron extends Varien_Object {
     
    public static $_newLock = false;
    
    public static $_closedLock = false;
    
    
    public function sendNewDeals() {
        
        if(self::$_newLock === true) {
            return;
        }    
        self::$_newLock = true;
        
        $newDeals = Mage::getModel('westum_deals/deal')->getCollection()
                ->addStatusFilter()
                ->getNewDeals();
        
        $template = $this->_getTemplate('new');
        $adminEmail = $this->_getAdminEmail();
        $emailSender = $this->_getEmailSender();       
       
        foreach($newDeals as $deal) {
            
            try {  
              if(Mage::getStoreConfig('westum_deals/emails/enable')) {
                                
                Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'frontend', 'store' => 0))
                    ->sendTransactional(
                       $template,
                       array(
                            'name' => Mage::getStoreConfig("trans_email/ident_{$emailSender}/name"),
                            'email'=> Mage::getStoreConfig("trans_email/ident_{$emailSender}/email")
                        ),
                       $adminEmail,
                      'Deals Manager',
                       array('deal' => $deal)
                    );
              }
                $deal->setNewEmail(true)->save(); 
             
            } catch(Exception $e) { 
                Mage::log("Error on sending new deal notification: Deal - #{$deal->getId()} with error mesage: {$e->getMessage()}",null,'magalterCronErrors.log',true);
            }          
        }
        
    }
    
    public function sendClosedDeals() {
        
        if(self::$_closedLock === true) {           
            return;
        }       
        self::$_closedLock = true;
        
        $closedDeals = Mage::getModel('westum_deals/deal')->getCollection()
                ->addStatusFilter()
                ->getClosedDeals();
      
        $template = $this->_getTemplate('closed');
        $adminEmail = $this->_getAdminEmail();
        $emailSender = $this->_getEmailSender();       
       
        foreach($closedDeals as $deal) {
            
            $deal->setUrl($this->_getDealUrl($deal->getId()));
            
            try {  
              if(Mage::getStoreConfig('westum_deals/emails/enable')) {
                                
                Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'frontend', 'store' => 0))
                    ->sendTransactional(
                       $template,
                       array(
                            'name' => Mage::getStoreConfig("trans_email/ident_{$emailSender}/name"),
                            'email'=> Mage::getStoreConfig("trans_email/ident_{$emailSender}/email")
                        ),
                       $adminEmail,
                      'Deals Manager',
                       array('deal' => $deal)
                    );
              }
                $deal->setCloseEmail(true)->save(); 
             
            } catch(Exception $e) { 
                Mage::log("Error on sending closed deal notification: Deal - #{$deal->getId()} with error mesage: {$e->getMessage()}",null,'magalterCronErrors.log',true);
            }          
        }
        
    }
    
    
    protected function _getEmailSender() {
        
        return  Mage::getStoreConfig('westum_deals/emails/email_sender');
        
    }
    
    protected function _getDealUrl($id) {
        
        return Mage::getUrl('westum_deals_admin/adminhtml_deals/edit',array('id'=>$id));
        
    }
    
    protected function _getTemplate($type = 'new') {
        
        return Mage::getStoreConfig("westum_deals/emails/{$type}");
        
    }
    
    protected function _getAdminEmail() {
         
        return  Mage::getStoreConfig('westum_deals/emails/admin_mail');
         
    }
    
    
}