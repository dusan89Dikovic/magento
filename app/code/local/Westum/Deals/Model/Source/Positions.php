<?php 
class Westum_Deals_Model_Source_Positions
{
    
    public static $options = array();
 
    public static function toOptionArray()
    {        
        
     $options = array(
         
         '-1' => array(
               'label' => Mage::helper('westum_deals')->__('Insert as CMS block or layout update'),
               'value' => '-1',
               'translation' => 'magalternoposition-false',
               'view' => 'deal',
               'layout' => false
                ),

         '0' => array(
               'label' => Mage::helper('westum_deals')->__('Right column top'),
               'value' => '0',
               'translation' => 'right-false',
                'view' => 'deal',
                'layout' => array('two_columns_right', 'three_columns')
                ),
         '1' => array(
               'label' => Mage::helper('westum_deals')->__('Right column bottom'),
               'value' => '1',
               'translation' => 'right-true',
               'view' => 'deal',
               'layout' => array('two_columns_right', 'three_columns')
                ),
          '2' => array(
               'label' => Mage::helper('westum_deals')->__('Content top'),
               'value' => '2',
               'translation' => 'content-false',
               'view' => 'cms_deal',
               'layout' => 'all'
                ),
          '3' => array(
               'label' => Mage::helper('westum_deals')->__('Content bottom'),
               'value' => '3',
               'translation' => 'content-true',
               'view' => 'cms_deal',
               'layout' => 'all'
               ),
          '4' => array(
               'label' => Mage::helper('westum_deals')->__('Shopping cart top'),
               'value' => '4',
               'translation' => 'checkout.cart-false',
               'view' => 'cms_deal',
               'layout' => 'all'
                ),
         '5' => array(
               'label' => Mage::helper('westum_deals')->__('Shopping cart bottom'),
               'value' => '5',
               'translation' => 'checkout.cart-true',
               'view' => 'cms_deal',
               'layout' => 'all'
                ),
         '6' => array(
               'label' => Mage::helper('westum_deals')->__('Left column top'),
               'value' => '6',
               'translation' => 'left-false',
               'view' => 'deal',
               'layout' => array('two_columns_left', 'three_columns')
                ),

           '7' => array(
               'label' => Mage::helper('westum_deals')->__('Left column bottom'),
               'value' => '7',
               'translation' => 'left-true',
               'view' => 'deal',
               'layout' => array('two_columns_left', 'three_columns')
                ),
          '8' => array(
               'label' => Mage::helper('westum_deals')->__('Customer account top'),
               'value' => '8',
               'translation' => 'my.account.wrapper-false',
               'view' => 'cms_deal',
               'layout' => 'all'
                ),
            '9' => array(
               'label' => Mage::helper('westum_deals')->__('Customer account bottom'),
               'value' => '9',
               'translation' => 'my.account.wrapper-true',
               'view' => 'cms_deal',
               'layout' => 'all'
                )         
          );
 
        return $options;
    }


    public static function toFlatArray() {

        $options = self::toOptionArray();
        $flatOptions = array();
        
            foreach($options as $option) { $flatOptions[$option['value']] = $option['label']; }
        
        return $flatOptions;
        
    }
    
    public static function getTranslation($id) {
        
        if(empty(self::$options)) {
            self::$options = self::toOptionArray();            
        }
        
        $translation = false;        
        if(isset(self::$options[$id])) {            
            $translation = self::$options[$id];            
        }
        
        if(!$translation) { return false; }
        
        $data = explode('-',$translation['translation']);
        
        $bool = $data[1] == 'true'?true:false;
         
        return array('block'=>$data[0],'position'=>$bool, 'view' => 'westum_deals/' . $translation['view'] . '.phtml');     
    
    }
    
    public static function getPositionsForLayout($code) {
        
        $values = array();         
        foreach(self::toOptionArray() as $position) {            
            if($position['layout'] == 'all' || (is_array($position['layout']) && in_array($code,$position['layout']))) {                
                $values[] = $position['value'];
            }
        }       
        return $values;       
    }

 
}
