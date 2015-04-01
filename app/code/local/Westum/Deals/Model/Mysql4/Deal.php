<?php

class Westum_Deals_Model_Mysql4_Deal extends Mage_Core_Model_Mysql4_Abstract
{
    
    public static $_updateRelated = false;
    
    public static $_customUpdate = array('stores','groups','positions','categories');
    
    protected $_object = null;
    
    protected function _construct() {
        
        $this->_init('westum_deals/deals', 'deal_id');     
        $this->_storeTable         = $this->getTable('westum_deals/deals_store');
        
    }
        
    
     protected function _afterSave(Mage_Core_Model_Abstract $object) {
         
         if(!self::$_updateRelated) { return $this; }
         
         $this->_object = $object;
         $this->_adapter = $this->_getWriteAdapter();         
         
         /* Update deals store table */         
         $stores = array_unique($object->getStoreIds());         
         if(in_array(0,$stores)) {             
            $stores = Mage::app()->getStore()->getCollection()->getAllIds();
         }
        
         if(in_array('stores',self::$_customUpdate)) {
            $this->_updateValues($this->_storeTable,'store_id',$stores); 
         }
        
    }
    
      public function loadStoreIds($object) {
          
        $dealId   = $object->getId();
        $storeIds = array();
        if ($dealId) {           
         
            $storeIds = $this->_getReadAdapter()->fetchCol(
                $this->_getReadAdapter()->select()
                    ->from($this->_storeTable, 'store_id')
                    ->where("{$this->getIdFieldName()} = :id_field"),
                array(':id_field' => $dealId)
            );        
        }
        
        $object->setStoreIds($storeIds);
       
    }
    
    protected function _updateValues($table, $key, $newValues) {
        
        if(is_string($newValues)) {            
            $newValues = Westum_Deals_Helper_Data::strToArray($newValues);            
        }
        if(is_int($newValues)) {            
            $newValues = (array) $newValues;
        }
        
        $condition = array("deal_id = ?" => $this->_object->getId());   
        
        $this->_adapter->delete($table, $condition);
        
         foreach ($newValues as $value) {            
                $data = array(
                     $key => $value,
                    'deal_id' => $this->_object->getId()
                );
              $this->_adapter->insert($table, $data);
         }
        
    }

 
}