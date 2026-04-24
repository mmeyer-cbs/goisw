<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CatalogPermission\Model\ResourceModel;

use Magento\Catalog\Model\Indexer\Category\Product\Processor;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class Category
 *
 * @package Bss\CatalogPermission\Model\ResourceModel
 */
class Category extends \Magento\Catalog\Model\ResourceModel\Category
{
    const COLUMN_NAME_ID_CE = 'entity_id';
    const COLUMN_NAME_ID_EE = 'row_id';
    const EE = 'Enterprise';
    const CE = 'Community';
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Get column name id
     *
     * @param string $edition
     * @return string
     */

    public function getColumnNameId($edition = '')
    {
        return $edition == self::EE ? self::COLUMN_NAME_ID_EE : self::COLUMN_NAME_ID_CE;
    }

    /**
     * Return children ids of category
     *
     * @param array $data
     * @param int $currentStoreId
     * @param boolean $recursive
     *
     * @return array
     */
    public function getChildrenIds($data, $currentStoreId, $recursive = true)
    {
        $linkField = $this->getLinkField();
        $attributeId = $this->getIsActiveAttributeId();
        $backendTable = $this->getTable([$this->getEntityTablePrefix(), 'int']);
        $backendTable2 = $this->getTable([$this->getEntityTablePrefix(), 'varchar']);
        $connection = $this->getConnection();
        $checkSql = $connection->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $bind = [
            'attribute_id' => $attributeId,
            'store_id' => $currentStoreId,
            'scope' => 1,
            'c_path' => $data['path'] . '/%',
        ];
        $select = $this->getConnection()->select()->from(
            ['m' => $this->getEntityTable()],
            $this->getColumnNameId()
        )->joinLeft(
            ['d' => $backendTable],
            "d.attribute_id = :attribute_id AND d.store_id = 0 AND d.{$linkField} = m.{$linkField}",
            []
        )->joinLeft(
            ['c' => $backendTable],
            "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.{$linkField} = m.{$linkField}",
            []
        )->joinLeft(
            ['v' => $backendTable2],
            "v.attribute_id = :attribute_id AND v.store_id = :store_id AND v.{$linkField} = m.{$linkField}",
            []
        )->where(
            $checkSql . ' = :scope'
        )->where(
            $connection->quoteIdentifier('path') . ' LIKE :c_path'
        );
        if (!$recursive) {
            $select->where($connection->quoteIdentifier('level') . ' <= :c_level');
            $bind['c_level'] = $data['level'] + 1;
        }

        return $connection->fetchCol($select, $bind);
    }

    /**
     * Get category collection by store
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $subCollection
     * @param int $customerGroupId
     * @param int $currentStoreId
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|mixed
     */
    public function getCategoryCollectionWithBssAttribute(
        $collection,
        $subCollection,
        $customerGroupId,
        $currentStoreId
    ) {
        $columnName = $this->getColumnNameId();
        $subCollection->getSelect()->join(
            ['cv' => $subCollection->getTable('catalog_category_entity_varchar')],
            'e.' . $columnName . ' = cv.' . $columnName . ''
        )->where("cv.attribute_id = (SELECT `attribute_id` FROM {$subCollection->getTable('eav_attribute')}
             WHERE `attribute_code` = 'bss_customer_group') AND FIND_IN_SET('{$customerGroupId}', cv.value)
         AND (cv.store_id = {$currentStoreId})");
        if ($subCollection->getAllIds()) {
            $storeConfigCategory = $subCollection->getAllIds();
            $storeConfigCategory = implode(',', $storeConfigCategory);
            $collection->getSelect()->join(
                ['cv' => $collection->getTable('catalog_category_entity_varchar')],
                'e.' . $columnName . ' = cv.' . $columnName . ''
            )->where("cv.attribute_id = (SELECT `attribute_id` FROM {$collection->getTable('eav_attribute')}
             WHERE `attribute_code` = 'bss_customer_group') AND FIND_IN_SET('{$customerGroupId}', cv.value)
             AND (cv.store_id = {$currentStoreId}
             OR (cv.store_id = 0 AND cv.' . $columnName . 'NOT IN ({$storeConfigCategory})))");
            return $collection;
        }

        return $this->getCategoryCollectionWithBssAttributeDefaultStore($collection, $customerGroupId);
    }

    /**
     * Get Category collection default store
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
     * @param int $customerGroupId
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|mixed
     */
    public function getCategoryCollectionWithBssAttributeDefaultStore($collection, $customerGroupId)
    {
        $columnName = $this->getColumnNameId();
        $collection->getSelect()->join(
            ['cv' => $collection->getTable('catalog_category_entity_varchar')],
            'e.' . $columnName . ' = cv.' . $columnName . ''
        )->where("cv.attribute_id = (SELECT `attribute_id` FROM {$collection->getTable('eav_attribute')}
             WHERE `attribute_code` = 'bss_customer_group') AND FIND_IN_SET('{$customerGroupId}', cv.value)
         AND (cv.store_id = 0)");

        return $collection;
    }

    /**
     * Get first parent category which been set catalog permision
     *
     * @param int $currentCategoryId
     * @return int|null
     */
    public function getFirstParentCategorySetPermission($currentCategoryId)
    {
        $columnName = $this->getColumnNameId();
        $connection = $this->getConnection();
        $select = $this->getConnection()->select()
            ->from(
                ['e' => $this->getTable('catalog_category_entity')],
                ['path']
            )
            ->where('e.' .$columnName . ' = ?', $currentCategoryId);
        $parentCateIds = explode('/', $connection->fetchOne($select));
        array_pop($parentCateIds);
        $select = $this->getConnection()->select()
            ->from(['varchar' => $this->getTable('catalog_category_entity_varchar')], $columnName)
            ->where('varchar.' . $columnName . ' IN (?)', $parentCateIds)
            ->where('value IS NOT NULL')
            ->where('varchar.attribute_id = 165');
        $parentCateIdsetPermission = $connection->fetchCol($select);
        $count  = count($parentCateIds);
        for ($i = 0; $i < $count; $i++) {
            foreach ($parentCateIdsetPermission as $permissionId) {
                if ($parentCateIds[$i] == $permissionId) {
                    return $permissionId;
                }
            }
        }
        return null;
    }
}
