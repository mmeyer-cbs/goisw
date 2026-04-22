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
declare(strict_types=1);

namespace Bss\ConfiguableGridView\Model\ConfigurableProduct\Model\Plugin\Frontend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DB\Sql\ExpressionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Cache of used products for configurable product
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UsedProductsCache
{
    /**
     * @var \Bss\ConfiguableGridView\Model\ResourceModel\Product\Type\Configurable
     */
    protected $productTypeConfigurable;

    /**
     * @var ExpressionFactory
     */
    protected $expressionFactory;

    /**
     * @var array
     */
    protected $skusIndex = [];

    /**
     * @var array
     */
    protected $skus = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param \Bss\ConfiguableGridView\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable
     * @param ExpressionFactory $expressionFactory
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param FrontendInterface $cache
     * @param SerializerInterface $serializer
     * @param ProductInterfaceFactory $productFactory
     * @param Session $customerSession
     */
    public function __construct(
        \Bss\ConfiguableGridView\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable,
        ExpressionFactory $expressionFactory,
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        FrontendInterface $cache,
        SerializerInterface $serializer,
        ProductInterfaceFactory $productFactory,
        Session $customerSession
    ) {
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->expressionFactory = $expressionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->productFactory = $productFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Retrieve used products for configurable product
     *
     * @param Configurable $subject
     * @param callable $proceed
     * @param Product $product
     * @param null|array $requiredAttributeIds
     * @return ProductInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetUsedProducts(
        Configurable $subject,
        callable $proceed,
        $product,
        $requiredAttributeIds = null
    ) {
        $cacheKey = $this->getCacheKey($product, $requiredAttributeIds);
        $usedProducts = $this->readUsedProductsCacheData($cacheKey);
        if ($usedProducts === null) {
            $usedProducts = $proceed($product, $requiredAttributeIds);
            $this->saveUsedProductsCacheData($product, $usedProducts, $cacheKey);
        } else {
            $this->setQuantityBssUsedProducts($product, $usedProducts, $cacheKey);
        }

        return $usedProducts;
    }

    /**
     * Set quantity(salable qty)
     *
     * @param Product $product
     * @param ProductInterface[]|null $requiredAttributeIds
     * @param string $cacheKey
     */
    public function setQuantityBssUsedProducts($product, $usedProducts, $cacheKey)
    {
        try {
            if ($this->productTypeConfigurable->isEnableMSI()) {
                if (($product->getData("allow_get_bss_quantity") || $this->productTypeConfigurable->allowGetQuantityBss) && $usedProducts[0]->getQuantityBss() === null) {
                    $product->setData("allow_get_bss_quantity", 0);
                    $this->productTypeConfigurable->allowGetQuantityBss = false;
                    $connection = $this->resourceConnection->getConnection();
                    $select = $connection->select();
                    if (count($this->skus)) {
                        $this->getSkus($usedProducts);
                    }
                    $select->from(
                        ["stock" => $this->productTypeConfigurable->getStockTable()],
                        ["stock.sku"]
                    )->where("stock.sku IN (?)", $this->skus);
                    $this->productTypeConfigurable->selectQuantityBss($select, "stock");
                    $data = $connection->query($select)->fetchAll();
                    if ($data) {
                        foreach ($data as $datum) {
                            $usedProducts[$this->skusIndex[$datum["sku"]]]->setQuantityBss($datum["quantity_bss"]);
                        }
                        $this->saveUsedProductsCacheData($product, $usedProducts, $cacheKey);
                    }
                }
            }
        } catch (\Exception $exception) {
        }
    }

    /**
     * Get skus, index skus in $usedProducts
     *
     * @param ProductInterface[] $usedProducts
     */
    public function getSkus($usedProducts)
    {
        $this->skusIndex = [];
        $this->skus = [];
        $index = 0;
        foreach ($usedProducts as $product) {
            $this->skusIndex[$product["sku"]] = $index;
            $this->skus[$product["sku"]] = $product["sku"];
            $index++;
        }
    }

    /**
     * Generate cache key for product
     *
     * @param Product $product
     * @param null $requiredAttributeIds
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCacheKey($product, $requiredAttributeIds = null): string
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $keyParts = [
            'getUsedProducts',
            $product->getData($metadata->getLinkField()),
            $product->getStoreId(),
            $this->customerSession->getCustomerGroupId(),
        ];
        if ($requiredAttributeIds !== null) {
            sort($requiredAttributeIds);
            $keyParts[] = implode('', $requiredAttributeIds);
        }
        $cacheKey = sha1(implode('_', $keyParts));

        return $cacheKey;
    }

    /**
     * Read used products data from cache
     *
     * Looking for cache record stored under provided $cacheKey
     * In case data exists turns it into array of products
     *
     * @param string $cacheKey
     * @return ProductInterface[]|null
     */
    private function readUsedProductsCacheData(string $cacheKey): ?array
    {
        $data = $this->cache->load($cacheKey);
        if (!$data) {
            return null;
        }

        $items = $this->serializer->unserialize($data);
        if (!$items) {
            return null;
        }

        $usedProducts = [];
        $index = 0;
        foreach ($items as $item) {
            /** @var Product $productItem */
            $productItem = $this->productFactory->create();
            $this->skusIndex[$item["sku"]] = $index;
            $this->skus[$item["sku"]] = $item["sku"];
            $productItem->setData($item);
            $usedProducts[] = $productItem;
            $index++;
        }

        return $usedProducts;
    }

    /**
     * Save $subProducts to cache record identified with provided $cacheKey
     *
     * Cached data will be tagged with combined list of product tags and data specific tags i.e. 'price' etc.
     *
     * @param Product $product
     * @param ProductInterface[] $subProducts
     * @param string $cacheKey
     * @return bool
     * @throws \Exception
     */
    private function saveUsedProductsCacheData(Product $product, array $subProducts, string $cacheKey): bool
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $data = $this->serializer->serialize(
            array_map(
                function ($item) {
                    return $item->getData();
                },
                $subProducts
            )
        );
        $tags = array_merge(
            $product->getIdentities(),
            [
                Category::CACHE_TAG,
                Product::CACHE_TAG,
                'price',
                Configurable::TYPE_CODE . '_' . $product->getData($metadata->getLinkField()),
            ]
        );
        $result = $this->cache->save($data, $cacheKey, $tags);

        return (bool) $result;
    }
}
