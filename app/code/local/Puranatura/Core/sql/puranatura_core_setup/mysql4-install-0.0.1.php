<?php 

$installer = $this;
$installer->startSetup();


// Add product Virtual SKU Yes/No attribute
$attribute_code = 'numb_of_descriptions';
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$numb_of_descriptions = Mage::getModel('catalog/resource_eav_attribute')
	->loadByCode('catalog_product', $attribute_code);

// If virtual sku product attribute exists, remove and recreate it
if($numb_of_descriptions !== null){
	$numb_of_descriptions->delete();
}
	$setup->addAttribute('catalog_product', $attribute_code, array(
		'label'             => 'Number of descriptions',
		'type'              => 'int',
		'input'             => 'select',
		'backend'           => 'eav/entity_attribute_backend_array',
		'frontend'          => '',
		'source'            => 'puranatura_core/source_option',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'           => true,
		'required'          => true,
		'user_defined'      => true,
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'option'            => array (
			'values' => array(
				1 => 'One',
			 	2 => 'Two',
				3 => 'Three',
 			 )
		),
		'visible_on_front'  => true,
		'visible_in_advanced_search' => false,
		'used_in_product_listing'    => true,
		'unique'            => false
	));
	
	// Add default value
	$numb_of_descriptions = Mage::getModel('eav/entity_attribute')
		->load($setup->getAttributeId('catalog_product',$attribute_code));
	
	$numb_of_descriptions
		->setDefaultValue($numb_of_descriptions->getSource()->getOptionId('One'))
		->save();
	
	// Add to attribute sets and groups
	$attributeSetCollection = Mage::getModel("eav/entity_attribute_set")->getCollection()->addFieldToFilter("entity_type_id", 4); 
	foreach($attributeSetCollection as $attributeSet){
		$setup->addAttributeToSet(Mage_Catalog_Model_Product::ENTITY, $attributeSet->getAttributeSetId(), 'General', $attribute_code);
	}

$installer->endSetup();