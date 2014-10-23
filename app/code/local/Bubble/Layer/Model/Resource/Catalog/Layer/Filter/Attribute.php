<?php
/**
 * Layer filter item resource rewrite.
 *
 * @category    Bubble
 * @package     Bubble_Layer
 * @version     1.3.3
 * @copyright   Copyright (c) 2014 BubbleCode (http://www.bubbleshop.net)
 */
class Bubble_Layer_Model_Resource_Catalog_Layer_Filter_Attribute
    extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute
{
    /**
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param mixed $value
     * @return Bubble_Layer_Model_Resource_Catalog_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        // Apply store filter to launch product limitations on collection which is observed by the module
        $collection->addStoreFilter($collection->getStoreId());
        $attribute  = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        if (array_key_exists($tableAlias, $collection->getSelect()->getPart(Zend_Db_Select::FROM))) {
            return $this;
        }
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            $connection->quoteInto("{$tableAlias}.value IN (?)", (array) $value)
        );

        $collection->getSelect()->join(
            array($tableAlias => $this->getMainTable()),
            implode(' AND ', $conditions),
            array()
        )->distinct();

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        // Redefining join type to allow multiple attribute values on currently filtered attribute
        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        foreach ($fromPart as $key => &$part) {
            if ($key == $filter->getAttributeModel()->getAttributeCode() . '_idx') {
                $part['joinType'] = Zend_Db_Select::LEFT_JOIN;
            }
        }
        unset($part);
        $select->reset(Zend_Db_Select::FROM);
        $select->setPart(Zend_Db_Select::FROM, $fromPart);

        $connection = $this->_getReadAdapter();
        $attribute  = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_index', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        );

        $select
            ->join(
                array($tableAlias => $this->getMainTable()),
                join(' AND ', $conditions),
                array('value', 'count' => new Zend_Db_Expr("COUNT(DISTINCT {$tableAlias}.entity_id)")))
            ->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }
}