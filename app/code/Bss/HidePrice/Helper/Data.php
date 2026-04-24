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
namespace Bss\HidePrice\Helper;

use Bss\HidePrice\Model\Config;
use Bss\HidePrice\Model\HidePrice;
use Bss\HidePrice\Model\Attribute\Source\HidePriceCustomer;
use Bss\HidePrice\Model\Config\Source\ApplyForChildProduct;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_ENABLED = 'bss_hide_price/general/enable';
    public const XML_PATH_SELECTOR = 'bss_hide_price/general/selector';
    public const XML_HIDE_PRICE_DISABLE_CHECKOUT_CONTROLLER = 'bss_hide_price/general/disable_checkout';
    public const XML_PATH_HIDE_PRICE_ACTION = 'bss_hide_price/hideprice_global/action';
    public const XML_HIDE_PRICE_CATEGORIES = 'bss_hide_price/hideprice_global/categories';
    public const XML_HIDE_PRICE_CUSTOMERS = 'bss_hide_price/hideprice_global/customers';
    public const XML_PATH_HIDE_PRICE_TEXT = 'bss_hide_price/hideprice_global/text';
    public const XML_PATH_HIDE_PRICE_URL = 'bss_hide_price/hideprice_global/hide_price_url';

    /**
     * Options HidePrice attribute
     */
    public const USER_GLOBAL_CONFIG = 0;
    public const DISABLE = -1;
    public const HIDE_PRICE_ADD_2_CART = 1;
    public const SHOW_PRICE_HIDE_ADD_2_CART = 2;

    /**
     * @var $customerGroupId
     */
    protected $customerGroupId;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Customer\Model\Session|\Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var null
     */
    private $store = null;

    /**
     * @var ConfigurableGridViewHelper
     */
    protected $cgvHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var HidePrice
     */
    protected $hidePrice;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $pr
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param ConfigurableGridViewHelper $cgvHelper
     * @param Config $config
     * @param HidePrice $hidePrice
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        ConfigurableGridViewHelper $cgvHelper,
        Config $config,
        HidePrice $hidePrice
    ) {
        $this->productRepository = $pr;
        parent::__construct($context);
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->storeManagerInterface = $storeManagerInterface;
        $this->configurableData = $configurableData;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->cgvHelper = $cgvHelper;
        $this->config = $config;
        $this->hidePrice = $hidePrice;
    }

    /**
     * Get Configurable grid view helper
     *
     * @return ConfigurableGridViewHelper
     */
    public function getCGVHelper()
    {
        return $this->cgvHelper;
    }

    /**
     * Get store
     *
     * @return \Magento\Store\Api\Data\StoreInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        if (!$this->store) {
            $this->store = $this->storeManagerInterface->getStore();
        }
        return $this->store;
    }

    /**
     * Is enable module
     *
     * @param int|null $store
     * @return mixed
     */
    public function isEnable($store = null)
    {
        return $this->config->isEnable($store);
    }

    /**
     * Retrieve Selector
     *
     * @param int $store
     * @return string
     */
    public function getSelector($store = null)
    {
        return $this->config->getSelector($store);
    }

    /**
     * Retrieve HidePrice Action
     *
     * @param int $store
     * @return string
     */
    public function getHidePriceAction($store = null)
    {
        return $this->config->getHidePriceAction($store);
    }

    /**
     * Retrieve HidePrice Categories
     *
     * @param int $store
     * @return string
     */
    public function getHidePriceCategories($store = null)
    {
        return $this->config->getHidePriceCategories($store);
    }

    /**
     * Get value attribute account login
     *
     * @return string|int
     */
    public function checkSpecificCustomerApply()
    {
        $customer = $this->customerSession->create();
        $customerId = $customer->getId();

        if (!$customer->getId()) {
            return HidePriceCustomer::BSS_HIDE_PRICE_USE_CONFIG;
        }

        return $this->hidePrice->checkSpecificCustomerApply($customerId);
    }

    /**
     * Retrieve HidePrice Customers
     *
     * @param int|null $store
     * @return string
     */
    public function getHidePriceCustomers($store = null)
    {
        return $this->config->getHidePriceCustomers($store);
    }

    /**
     * Get hide price url
     *
     * @param int|null $storeId
     * @return string
     */
    public function getHidePriceUrlConfig($storeId = null)
    {
        return $this->config->getHidePriceUrlConfig($storeId);
    }

    /**
     * Get config disable checkout
     *
     * @param int|null $store
     * @return mixed
     */
    public function getDisableCheckout($store = null)
    {
        return $this->config->getDisableCheckout($store);
    }

    /**
     * Retrieve Item Product
     *
     * @param int $itemId
     * @return object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemProduct($itemId)
    {
        return $this->productRepository->getById($itemId, false);
    }

    /**
     * Call Product
     *
     * @return object
     */
    public function callProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * Get Customer GroupId
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->customerGroupId) {
            return $this->customerGroupId;
        }
        $customer = $this->customerSession->create();
        if ($customer->getId()) {
            return $customer->getCustomer()->getGroupId();
        }
        return 0;
    }

    /**
     * Get Hide Price Message
     *
     * @param object $product
     * @param bool $includeUrl
     * @return string
     */
    public function getHidepriceMessage($product, $includeUrl = true)
    {
        $message = $this->getHidePriceText($product);

        if ($this->getHidePriceUrl($product) && $includeUrl) { //product have hide price url
            return '<a href="' . trim($this->getHidePriceUrl($product)) . '">' . $message . '</a>';
        } else {
            return $message;
        }
    }

    /**
     * Get hide price link
     *
     * @param Product $product
     * @param bool $includeUrl
     * @return array|string
     */
    public function getHidepriceMessageLink($product, $includeUrl = true)
    {
        $message = $this->getHidePriceText($product);

        if ($this->getHidePriceUrl($product) && $includeUrl) { //product have hide price url
            return ['link' => trim($this->getHidePriceUrl($product)), 'message' => $message];
        } else {
            return $message;
        }
    }

    /**
     * Get Hide Price Action Product
     *
     * @param object $product
     * @return string
     */
    public function getHidePriceActionProduct($product)
    {
        return $product->getHidepriceAction();
    }

    /**
     * Get Hide Text
     *
     * @param object $product
     * @return string
     */
    public function getHidePriceText($product)
    {
        return $this->config->getHidePriceText($product);
    }

    /**
     * Get hide price text global
     *
     * @param int|null $storeId
     * @return string
     */
    public function getHidePriceTextGlobal($storeId = null)
    {
        return $this->config->getHidePriceTextGlobal($storeId);
    }

    /**
     * Get Hide Url
     *
     * @param object $product
     * @return string
     */
    public function getHidePriceUrl($product)
    {
        return $this->config->getHidePriceUrl($product);
    }

    /**
     * Filter Array
     *
     * @param string $string
     * @return array
     */
    public function filterArray($string)
    {
        return $this->hidePrice->filterArray($string);
    }

    /**
     * Active Hide Price
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param mixed $storeId
     * @param bool $isChild
     * @param bool $cusGroupId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function activeHidePrice($product, $storeId = null, $isChild = false, $cusGroupId = false)
    {
        $customer = $this->customerSession->create();
        $customerId = $customer->getId();
        $customerGroup = $this->getCustomerGroupId();
        return $this->hidePrice->activeHidePrice(
            $product,
            $customerId,
            $customerGroup,
            $storeId,
            $isChild,
            $cusGroupId
        );
    }

    /**
     * Active HidePrice Grouped Product
     *
     * @param object $product
     * @param int $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function activeHidePriceGrouped($product, $storeId = null)
    {
        if ($product->getTypeId() != "grouped") {
            return true;
        }
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        $hasAssociatedProducts = count($associatedProducts) > 0;
        if ($hasAssociatedProducts) {
            foreach ($associatedProducts as $item) {
                $childProduct = $this->productRepository->getById($item->getId());
                if (!$this->activeHidePrice($childProduct, $storeId)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Hide Price Action Active
     *
     * @param object $product
     * @return int|string
     */
    public function hidePriceActionActive($product)
    {
        if ($this->isEnable()) {
            if ($product->getHidepriceAction() == -1) {
                return 0;
            } elseif ($product->getHidepriceAction() == 0 || $product->getHidepriceAction() == '') {
                return $this->getHidePriceAction();
            } else {
                return $product->getHidepriceAction();
            }
        } else {
            return 0;
        }
    }

    /**
     * Check Hide Price Child Product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $requestInfo
     * @return int|string|null
     */
    public function hidePriceChildProduct($product, $requestInfo)
    {
        $childProduct = $this->configurableData->getProductByAttributes($requestInfo['super_attribute'], $product);
        if ($childProduct) {
            return $this->hidePriceActionActive($childProduct);
        }
        return null;
    }

    /**
     * Get all data from configurable product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllData($product)
    {
        $result = [];
        $productEntityId = $product->getId();
        $parentProduct = $this->configurableData->getChildrenIds($productEntityId);
        if ($this->activeHidePrice($product)) {
            if ($this->hidePriceActionActive($product) == 1) {
                $result['hide_price_parent'] = true;
            } elseif ($this->hidePriceActionActive($product) == 2) {
                $result['hide_price_parent'] = false;
            }
            $result['hide_price_parent_content'] = '<p id="hide_price_text" class="hide_price_text">'
                . $this->getHidepriceMessage($product) . '</p>';
        }
        if ($product->getHidepriceApplychild() !== ApplyForChildProduct::BSS_HIDE_PRICE_NO) {
            $result['hide_price_apply_child'] = true;
        }

        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        $result['entity'] = $productEntityId;

        foreach ($parentProduct[0] as $simpleProduct) {
            $childProduct = [];
            $childProduct['entity'] = $simpleProduct;
            $child = $this->productRepository->getById($childProduct['entity']);
            $childProduct['hide_price'] = $this->activeHidePrice($child);
            if ($childProduct['hide_price']) {
                $childProduct['hide_price_content'] = '<p id="hide_price_text_' . $child->getId()
                    . '" class="hide_price_text">' . $this->getHidepriceMessage($child) . '</p>';
                $childProduct['show_price'] = $this->hidePriceActionActive($child) == 2;
            } else {
                $childProduct['hide_price_content'] = false;
                $childProduct['show_price'] = false;
            }
            $key = '';
            foreach ($parentAttribute as $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                $key .= $child->getData($attrLabel) . '_';
            }
            $result['child'][$key] = $childProduct;
        }
        $result['parent_id'] = $product->getId();
        $result['selector'] = $this->getSelector();
        return $result;
    }

    /**
     * Check hide price for customer group
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool|int
     */
    private function hidePriceCustomersGroupProduct($product)
    {
        $customer = $this->customerSession->create();
        $customerId = $customer->getId();
        $customerGroup = $this->getCustomerGroupId();
        return $this->hidePrice->hidePriceCustomersGroupProduct($product, $customerId, $customerGroup);
    }

    /**
     * Is enable hide price global config
     *
     * @param object $product
     * @return bool|int
     */
    private function hidePriceCustomersGroupGlobal($product)
    {
        $customer = $this->customerSession->create();
        $customerId = $customer->getId();
        $customerGroup = $this->getCustomerGroupId();
        return $this->hidePrice->hidePriceCustomersGroupGlobal($product, $customerId, $customerGroup);
    }

    /**
     * Get value customer attribute
     *
     * @param int $customerId
     * @return string
     */
    public function getAttributeHidePrice($customerId)
    {
        return $this->hidePrice->getAttributeHidePrice($customerId);
    }
}
