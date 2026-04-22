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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model\ResourceModel\Product;

use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class GetConfigButton
 *
 * @package Bss\QuoteExtension\Model\ResourceModel\Product
 */
class GetConfigButton extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        ProductMetadataInterface $productMetadata,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->productMetadata = $productMetadata;
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_entity_int', 'value_id');
    }

    /**
     * Get Config Button Enable For Category
     *
     * @param int $storeId
     * @param int $requestConfigCategoryId
     * @param int $categoryId
     * @return int
     * @throws \Zend_Db_Statement_Exception
     */
    public function getEnableButtonCategory($storeId, $requestConfigCategoryId, $categoryId)
    {
        $entityIntTable = $this->getTable('catalog_category_entity_int');
        $data = $this->querySelect(
            $entityIntTable,
            $requestConfigCategoryId,
            $storeId,
            $categoryId
        );
        if (!empty($data)) {
            return (int)$data[0];
        }
        return false;
    }

    /**
     * Get Config Button Customer Group For Category
     *
     * @param int $storeId
     * @param int $categoryCustomerGroupId
     * @param int $categoryId
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getEnableButtonCustomerGroup(
        $storeId,
        $categoryCustomerGroupId,
        $categoryId
    ) {
        $entityTextTable = $this->getTable('catalog_category_entity_text');
        $data = $this->querySelect(
            $entityTextTable,
            $categoryCustomerGroupId,
            $storeId,
            $categoryId
        );
        if (!empty($data)) {
            if ($data[0] == '') {
                return [];
            }
            return explode(",", $data[0]);
        }
        return [];
    }

    /**
     * Query Datbase
     *
     * @param string $table
     * @param int $attributeId
     * @param int $storeId
     * @param int $categoryId
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    protected function querySelect(
        $table,
        $attributeId,
        $storeId,
        $categoryId
    ) {
        $data = [];
        $connection = $this->getConnection();
        $idRow = ($this->productMetadata->getEdition() == 'Community') ? 'entity_id = ?' : 'row_id = ?';
        $select = $connection->select()
            ->from($table)
            ->where('attribute_id = ?', $attributeId)
            ->where('store_id = ?', $storeId)
            ->where($idRow, $categoryId);

        $query = $connection->query($select);
        if ($query) {
            while ($row = $query->fetch()) {
                array_push($data, $row['value']);
            }
        }
        return $data;
    }
}
