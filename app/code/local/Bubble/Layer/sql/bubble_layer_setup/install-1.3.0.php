<?php
/**
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();

$entityTypeId       = $installer->getEntityTypeId('catalog_category');
$attributeSetId     = $installer->getDefaultAttributeSetId($entityTypeId);

// Add category attribute to allow product attributes show/hide on category level
$installer->addAttribute($entityTypeId, 'layer_product_attributes', array(
    'input'             => 'multiselect',
    'type'              => 'text',
    'label'             => 'Layer Product Attributes',
    'source'            => 'bubble_layer/catalog_category_attribute_source_layer_product_attributes',
    'backend'           => 'bubble_layer/catalog_category_attribute_backend_layer_product_attributes',
    'required'          => 1,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => 1,
    'input_renderer'    => 'adminhtml/catalog_category_helper_sortby_available',
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    'Display Settings',
    'layer_product_attributes',
    100
);

$installer->endSetup();
