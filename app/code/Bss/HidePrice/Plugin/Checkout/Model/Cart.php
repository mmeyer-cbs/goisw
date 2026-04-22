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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin\Checkout\Model;

use Bss\HidePrice\Model\Config\Source\ApplyForChildProduct;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Swatches\Helper\Data as SwatchData;

/**
 * Class Cart
 *
 * @package Bss\HidePrice\Plugin\Checkout\Model
 */
class Cart
{
    /**
     * Data
     *
     * @var \Bss\HidePrice\Helper\Data
     */
    protected $helper;

    /**
     * ProductRepositoryInterface
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var SwatchData
     */
    private $swatchHelper;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    protected $configurableProductHelper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogHelper;

    /**
     * Cart constructor.
     *
     * @param SwatchData $swatchData
     * @param \Bss\HidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param Configurable $configurable
     * @param \Magento\ConfigurableProduct\Helper\Data $configurableProductHelper
     * @param \Magento\Catalog\Helper\Product $catalogHelper
     * @param \Magento\Framework\Message\ManagerInterface $managerMessage
     */
    public function __construct(
        SwatchData $swatchData,
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        Configurable $configurable,
        \Magento\ConfigurableProduct\Helper\Data $configurableProductHelper,
        \Magento\Catalog\Helper\Product $catalogHelper,
        \Magento\Framework\Message\ManagerInterface $managerMessage
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configurable = $configurable;
        $this->messageManager = $managerMessage;
        $this->swatchHelper = $swatchData;
        $this->configurableProductHelper = $configurableProductHelper;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Check hide price product before add product to cart
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return array
     * @throws LocalizedException|NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        if (!$this->helper->isEnable()) {
            return [$productInfo, $requestInfo];
        }
        $requestInformation = $requestInfo;
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $requestInfo = $requestInformation->getData();
        }
        $product = $this->getProduct($productInfo);
        if (isset($requestInfo['quoteextension'])
            && $requestInfo['quoteextension'] == 1
            && $product->getIsActiveRequest4Quote()
        ) {
            return [$productInfo, $requestInformation];
        }
        $activeHidePrice = $this->helper->activeHidePrice($product);
        $hidePriceActionActive = $this->helper->hidePriceActionActive($product);
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $hidePriceChildProduct = $this->helper->hidePriceChildProduct($product, $requestInfo);
        } else {
            $hidePriceChildProduct = 1;
        }

        if ($activeHidePrice && $hidePriceActionActive && $hidePriceChildProduct) {
            $dataMessage = $this->helper->getHidepriceMessageLink($product);
            if (is_array($dataMessage)) {
                throw new LocalizedException(__($dataMessage["message"]));
            } else {
                throw new LocalizedException(__($dataMessage));
            }
        }
        $productType = $product->getTypeId();
        if ($productType == 'grouped'
            && (!$activeHidePrice || !$hidePriceActionActive)
            && isset($requestInfo['super_group']) && is_array($requestInfo['super_group'])
        ) {
            $itemGroupAddToCart = $requestInfo['super_group'];
            foreach ($itemGroupAddToCart as $key => $qty) {
                if ($qty > 0) {
                    $itemGroupAddToCartIds[] = $key;
                }
            }
            $errorMessage = '';
            if (isset($itemGroupAddToCartIds)) {
                $collection = $this->getProductCollection($itemGroupAddToCartIds);
                foreach ($collection as $item) {
                    if ($this->helper->activeHidePrice($item)) {
                        $itemGroupAddToCart[$item->getId()] = 0;
                        if ($errorMessage != '') {
                            $errorMessage .= ', ';
                        }
                        $errorMessage .= $item->getName();
                    }
                }
                $requestInfo['super_group'] = $itemGroupAddToCart;
            }
            if ($errorMessage != '') {
                $this->messageManager->addErrorMessage(
                    __('Admin does not allow you add to cart product ' . $errorMessage . ' to cart !')
                );
            }
            return [$product, $requestInfo];
        } elseif ($productType === Configurable::TYPE_CODE) {
            if (!isset($requestInfo['super_attribute'])) {
                if ($attributesData = $this->swatchHelper->getSwatchAttributesAsArray($product)) {
                    foreach ($attributesData as $key => $val) {
                        $requestInfo['super_attribute'][$key] = "";
                    }
                }
                if ($options = $this->configurableProductHelper
                    ->getOptions($product, $this->getAllowProducts($product))
                ) {
                    foreach ($options as $attributeId => $val) {
                        if ($attributeId !== "index") {
                            $requestInfo['super_attribute'][$attributeId] = "";
                        }
                    }
                }
            }
            $optionsAddToCart = $requestInfo['super_attribute'];
            if ($childProduct = $this->configurable->getProductByAttributes($optionsAddToCart, $product)) {
                if ($product->getHidepriceApplychild() !== ApplyForChildProduct::BSS_HIDE_PRICE_NO) {
                    $productCheck = $product;
                } else {
                    $productCheck = $childProduct;
                }

                if ($this->helper->activeHidePrice($productCheck)) {
                    $dataMessage = $this->helper->getHidepriceMessageLink($productCheck);
                    if (is_array($dataMessage)) {
                        throw new LocalizedException(__($dataMessage["message"]));
                    } else {
                        throw new LocalizedException(__($dataMessage));
                    }
                }
            }
        } elseif ($productType == 'bundle'
            && (!$activeHidePrice || !$hidePriceActionActive)
            && isset($requestInfo['bundle_option']) && is_array($requestInfo['bundle_option'])
            && $product->getPriceType() == Price::PRICE_TYPE_DYNAMIC
        ) {
            $optionIds = $requestInfo['bundle_option'];
            $selectionCollection = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );
            $errorMessage = '';
            foreach ($selectionCollection as $child) {
                if (isset($optionIds[$child->getOptionId()])
                    && $optionIds[$child->getOptionId()] == $child->getSelectionId()
                ) {
                    if ($this->helper->activeHidePrice($child)
                        && ($this->helper->hidePriceActionActive($child) == 2 || $this->helper->hidePriceActionActive($child) == 1)
                    ) {
                        $product->setSkipCheckRequiredOption(true);
                        $requestInfo['bundle_option'][$child->getOptionId()] = '';
                        if ($errorMessage != '') {
                            $errorMessage .= ', ';
                        }
                        if (isset($requestInfo['bundle_option_qty'][$child->getOptionId()])) {
                            $errorMessage .= $child->getName();
                        }
                    }
                }
            }
            if ($errorMessage != '') {
                $this->messageManager->addErrorMessage(
                    __('Admin does not allow you add to cart product ' . $errorMessage . ' to cart !')
                );
            }
            return [$product, $requestInfo];
        }
        return [$productInfo, $requestInformation];
    }

    /**
     * Get AllowProducts
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $currentProduct
     * @return Product[]|mixed
     * @throws NoSuchEntityException
     */
    protected function getAllowProducts($currentProduct)
    {
        $products = [];
        $skipSaleableCheck = $this->catalogHelper->getSkipSaleableCheck();
        $allProducts = $currentProduct->getTypeInstance()->getUsedProducts($currentProduct, null);

        $helper = $this->helper->getCGVHelper();
        foreach ($allProducts as $product) {
            if ($helper->getConfig('stock_availability') && $helper->getConfig('out_stock')) {
                $products[] = $product;
            } else {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
        }
        return $products;
    }

    /**
     * Get product object based on requested product information
     *
     * @param Product|int|string $productInfo
     * @return Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Product) {
            $product = $productInfo;
            if (!$product->getId()) {
                throw new LocalizedException(__('We can\'t find the product.'));
            }
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $storeId = $this->helper->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productInfo, false, $storeId);
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('We can\'t find the product.'), $e);
            }
        } else {
            throw new LocalizedException(__('We can\'t find the product.'));
        }
        $currentWebsiteId = $this->helper->getStore()->getWebsiteId();
        if (!is_array($product->getWebsiteIds()) || !in_array($currentWebsiteId, $product->getWebsiteIds())) {
            throw new LocalizedException(__('We can\'t find the product.'));
        }
        return $product;
    }

    /**
     * @param array $ids
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    protected function getProductCollection($ids)
    {
        return $this->productCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in' => $ids]);
    }
}
