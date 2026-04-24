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
 * @package    Bss_ConfiguableGridView
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfiguableGridView\Model\ResourceModel\Product\Type;

use Bss\ConfiguableGridView\Helper\Data;
use Bss\ConfiguableGridView\Model\ResourceModel\Product\InventoryStock;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\ExpressionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Statement_Exception;

/**
 * Configurable
 */
class Configurable
{
    /**
     * @var bool
     */
    public $allowGetQuantityBss = false;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var InventoryStock
     */
    private $inventoryStock;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ExpressionFactory
     */
    protected $expressionFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Construct.
     *
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param InventoryStock $inventoryStock
     * @param ResourceConnection $resource
     * @param ExpressionFactory $expressionFactory
     * @param  \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry $registry
     * @param Data $helperData
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        InventoryStock $inventoryStock,
        ResourceConnection $resource,
        ExpressionFactory $expressionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        Data $helperData
    ) {
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->inventoryStock = $inventoryStock;
        $this->resource = $resource;
        $this->expressionFactory = $expressionFactory;
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->registry = $registry;
        $this->helperData = $helperData;
    }

    /**
     * After get used
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @param mixed $result
     * @return mixed
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetUsedProductCollection(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,
        $result
    ) {
        if ($this->allowGetQuantityBss && $this->isEnableMSI()) {
            $this->allowGetQuantityBss = false;
            $stockId = $this->getStockId();
            if ($stockId) {
                $stockTable = $this->inventoryStock->getStockTableName($stockId);
                $result->getSelect()->joinInner(
                    ['stock' => $stockTable],
                    'stock.sku = e.sku'
                );

                $this->selectQuantityBss($result->getSelect(), "e");
                // issue: https://github.com/magento/magento2/issues/22379
                if ($this->helperData->isShowConfig('out_stock')) {
                    $result->setFlag('has_stock_status_filter', true);
                }
            }
        }

        return $result;
    }

    /**
     * Check table exists or not Query
     *
     * @param string $nameTable
     * @return bool
     */
    public function isTableExistsOrNot($nameTable)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName($nameTable);
        return $connection->isTableExists($tableName);
    }

    /**
     * Select quantity(salable qty) by stock table and inventory_reservation table
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $tableMain
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function selectQuantityBss($select, $tableMain)
    {
        $stockId = $this->getStockId();
        if ($stockId) {
            $connection = $this->resource->getConnection();
            $table_sales_stock = $this->resource->getTableName('inventory_reservation');
            $query = $connection->select()->from($table_sales_stock, ['quantity' => 'SUM(quantity)'])
                ->where(sprintf("sku = %s.sku", $tableMain))
                ->where('stock_id = ?', $stockId)
                ->limit(1);
            $select->columns([
                'quantity_bss' => $this->expressionFactory->create(
                    ['expression' => 'IFNULL((' . $query . '),0) + stock.quantity']
                )
            ]);
        }
    }

    /**
     * Get Stock id current
     *
     * @return bool|int|mixed
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function getStockId()
    {
        $stockId = 0;
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $checkExistTable = $this->isTableExistsOrNot(
            $this->resource->getTableName('inventory_stock_sales_channel')
        );
        if ($checkExistTable) {
            $stockId = $this->inventoryStock->getStockIdByWebsiteCode($websiteCode);
        }
        return $stockId;
    }

    /**
     * Get stock table current
     *
     * @return false|string
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function getStockTable()
    {
        $stockId = $this->getStockId();
        if ($stockId) {
            return $this->inventoryStock->getStockTableName($stockId);
        }
        return false;
    }

    /**
     * Get used product configurable
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array|null $requiredAttributeIds
     * @return mixed
     */
    public function getUsedProductsConfigurable($product, $requiredAttributeIds = null)
    {
        $this->allowGetQuantityBss = true;
        $usedProducts = $product->getTypeInstance()->getUsedProducts($product, $requiredAttributeIds);
        $this->allowGetQuantityBss = false;
        return $usedProducts;
    }

    /**
     * Check enable disable MSI
     *
     * @return bool
     */
    public function isEnableMSI()
    {
        $version = $this->productMetadata->getVerSion();
        if (version_compare($version, '2.3.0') >= 0 && $this->moduleManager->isEnabled('Magento_Inventory')) {
            return true;
        }
        return false;
    }
}
