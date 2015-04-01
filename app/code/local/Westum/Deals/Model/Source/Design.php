<?php
 
class Westum_Deals_Model_Source_Design {
    
    const PACKAGE_PATH = 'frontend/magalter_design';
    
    protected static $_packages = array();
    
    const IMAGE_RESIZE = '70';
    
    const IMAGE_RESIZE_H = '70';
    
    const IMAGE_CMS = '70'; 
    
    const IMAGE_CMS_RESIZE_H = '70';
     
    public static function toOptionArray() {
 
        $options = array();        
        $packages = self::getPackages();
       
        foreach ($packages as $package) {
            
            if($package->getStatus() != '1') {
                continue;
            }   
            
            $options[] = array(
               'label' => $package->getLabel(),
               'value' => $package->getId()
            );
        }

        return $options;
    }

    public static function toFlatArray() {

        $options = self::toOptionArray();
        $flatOptions = array();

            foreach($options as $option) { $flatOptions[$option['value']] = $option['label']; }

        return $flatOptions;

    }
    
    public static function getPackages() {
         
        $types = array();
        $config = Mage::getConfig()->getNode(self::PACKAGE_PATH);
        if ($config) {
            foreach ($config->children() as $type=>$node) {
               
                $types[$type] = new Varien_Object(array(
                    'id'            => $type,
                    'label'    => Mage::helper('westum_deals')->__((string)$node->label),
                    'description'   => Mage::helper('westum_deals')->__((string)$node->description),
                    'path'          => (string) $node->path,
                    'status'        => $node->status,
                ));
            }
        }
        return $types;
       
    }
}
