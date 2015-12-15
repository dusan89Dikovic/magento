<?php 

$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$attributes = array(
		'description_version_2' => array(
				'type'                       => 'text',
				'backend'                    => '',
				'frontend'                   => '',
				'label'                      => 'Product Description Version 2',
				'input'                      => 'textarea',
				'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
				'visible'                    => true,
				'required'                   => false,
				'user_defined'               => true,
				'searchable'                 => false,
				'filterable'                 => false,
				'comparable'                 => false,
				'visible_on_front'           => true,
				'visible_in_advanced_search' => true,
				'used_in_product_listing'    => true,
				'unique'                     => false,
		),
		'description_version_3' => array(
				'type'                       => 'text',
				'backend'                    => '',
				'frontend'                   => '',
				'label'                      => 'Product Description Version 3',
				'input'                      => 'textarea',
				'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
				'visible'                    => true,
				'required'                   => false,
				'user_defined'               => true,
				'searchable'                 => false,
				'filterable'                 => false,
				'comparable'                 => false,
				'visible_on_front'           => true,
				'visible_in_advanced_search' => true,
				'used_in_product_listing'    => true,
				'unique'                     => false,
		)
);

foreach ($attributes as $code => $config) {
	$installer->addAttribute($entityTypeId, $code, $config);
}

// Add to attribute sets and groups
$attributeSetCollection = Mage::getModel('eav/entity_attribute_set')->getCollection()->addFieldToFilter('entity_type_id', $entityTypeId);
foreach ($attributes as $code => $config) {
	foreach($attributeSetCollection as $attributeSet){
		$installer->addAttributeToSet(Mage_Catalog_Model_Product::ENTITY, $attributeSet->getAttributeSetId(), 'General', $code);
	}
}

$installer->endSetup();