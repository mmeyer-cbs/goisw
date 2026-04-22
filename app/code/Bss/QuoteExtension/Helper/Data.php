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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Helper;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Statement_Exception;

/**
 * Class Data
 *
 * @package Bss\QuoteExtension\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    const PRODUCT_CONFIG_ENABLE = 1;
    const PRODUCT_CONFIG_DISABLE = 2;
    const PATH_REQUEST4QUOTE_ENABLED = 'bss_request4quote/general/enable';
    const PATH_REQUEST4QUOTE_ICON = 'bss_request4quote/request4quote_global/icon';
    const PATH_REQUEST4QUOTE_VALIDATE_QTY = 'bss_request4quote/request4quote_global/validate_qty_product';
    const PATH_REQUEST4QUOTE_QUOTABLE = 'bss_request4quote/request4quote_global/quotable';
    const PATH_REQUEST4QUOTE_APPLY_CUSTOMER = 'bss_request4quote/request4quote_global/customers';
    const PATH_REQUEST4QUOTE_DISABLE_RESUBMIT = 'bss_request4quote/request4quote_global/disable_resubmit';
    const PATH_REQUEST4QUOTE_ITEMS_COMMENT = 'bss_request4quote/request4quote_global/disable_items_comment';
    const PATH_REQUEST4QUOTE_SHIPPING_REQUIRED = 'bss_request4quote/request4quote_global/shipping_required';

    const BSS_CONFIGURABLE_GRID_VIEW_ENABLE_CONFIG_XML_PATH = 'configuablegridview/general/active';
    const BSS_CONFIGURABLE_GRID_VIEW_DISABLED_CUSTOMER_GROUP = 'configuablegridview/general/disabled_customer_group';
    const XML_PATH_ENABLED_COMPANY_ACCOUNT = 'bss_company_account/general/enable';
    const PATH_SALES_REP_ENABLED = 'bss_salesrep/general/enable';

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var  StoreManagerInterface $storeManagerInterface
     */
    protected $storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var View
     */
    protected $customerHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var ConfigRequestButton
     */
    protected $helperConfig;

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManagerInterface
     * @param CurrencyFactory $currencyFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param View $customerHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param TimezoneInterface $localeDate
     * @param ConfigRequestButton $helperConfig
     * @param DesignInterface $design
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param CustomerSession $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResolverInterface  $resolver,
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        CurrencyFactory $currencyFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        View $customerHelper,
        CustomerRepositoryInterface $customerRepository,
        TimezoneInterface $localeDate,
        ConfigRequestButton $helperConfig,
        DesignInterface $design,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        CustomerSession $customerSession
    ) {
        $this->resolver = $resolver;
        parent::__construct($context);
        $this->storeManager = $storeManagerInterface;
        $this->currencyFactory = $currencyFactory;
        $this->priceCurrency = $priceCurrency;
        $this->pricingHelper = $pricingHelper;
        $this->customerHelper = $customerHelper;
        $this->customerRepository = $customerRepository;
        $this->localeDate = $localeDate;
        $this->helperConfig = $helperConfig;
        $this->design = $design;
        $this->jsonSerializer = $jsonSerializer;
        $this->customerSession = $customerSession;
    }

    /**
     * Is Enable Module
     *
     * @param int $store
     * @return bool
     */
    public function isEnable($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_REQUEST4QUOTE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Customer Group Id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->helperConfig->getCustomerGroupId();
    }

    /**
     * Check Resubmit Quote Enable
     *
     * @param int $store
     * @return bool
     */
    public function disableResubmit($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_REQUEST4QUOTE_DISABLE_RESUBMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get quoteable
     *
     * @param int $store
     * @return int
     */
    public function getQuotable($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_QUOTABLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Apply For Customer Group
     *
     * @param int $store
     * @return array
     */
    public function getApplyForCustomers($store = null)
    {
        return $this->toArray($this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_APPLY_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
    }

    /**
     * Is Enable Quote Item Comment
     *
     * @param int $store
     * @return array
     */
    public function isEnableQuoteItemsComment($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_ITEMS_COMMENT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check Apply Default Quantity Condition When Adding Product to Quote
     *
     * @param int $store
     * @return bool
     */
    public function validateQuantity($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_VALIDATE_QTY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Icon For Request Quote
     *
     * @param int $store
     * @return Phrase|string
     * @throws NoSuchEntityException
     */
    public function getIcon($store = null)
    {
        $image = '';
        $pointImage = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_ICON,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($pointImage) {
            $imageSrc = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'bss/request4quote/' . $pointImage;
            $image = __('<img src="%1" alt="request4quote-icon"/>', $imageSrc);
        }
        return $image;
    }

    /**
     * Get date GTM
     *
     * @param null|string $date
     * @return \DateTime
     */
    public function getDate($date = null)
    {
        return $this->localeDate->date($date, null, false);
    }

    /**
     * Get Current Date
     *
     * @return string
     */
    public function getCurrentDate()
    {
        return $this->getDate()->format('Y-m-d');
    }

    /**
     * Get Current Date Time
     *
     * @return string
     */
    public function getCurrentDateTime()
    {
        return $this->getDate()->format('Y-m-d H:i:s');
    }

    /**
     * Convert Date expired compatible all time zone, locale
     *
     * @param null|string $date
     * @return null|string
     */
    public function convertDateExpired($date = null)
    {
        if ($date) {
            $date = strtotime($date . " + 11 hours");
            return $this->getDate($date)->format("Y-m-d H:i:s");
        }
        return null;
    }

    /**
     * Is active Request4quote for product
     *
     * @param ProductInterface $product
     * @param int $storeId
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function isActiveRequest4Quote($product, $storeId = null)
    {
        if ($this->isEnable($storeId)) {
            $customerGroup = $this->getCustomerGroupId();
            return $this->isActiveForProduct($product, $storeId, $customerGroup);
        }
        return false;
    }

    /**
     * Is active Request4quote for Product Config
     *
     * @param ProductInterface $product
     * @param int $storeId
     * @param int $customerGroup
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    protected function isActiveForProduct($product, $storeId, $customerGroup)
    {
        if ($product->getBssRequestQuote() == self::PRODUCT_CONFIG_DISABLE) {
            return false;
        } elseif ($product->getBssRequestQuote() == self::PRODUCT_CONFIG_ENABLE) {
            return $this->isActiveProductConfig($product, $customerGroup);
        }
        return $this->isActiveForCategory($product, $storeId, $customerGroup);
    }

    /**
     * Is active Request4quote for Category Config
     *
     * @param ProductInterface $product
     * @param int $storeId
     * @param int $customerGroup
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    protected function isActiveForCategory($product, $storeId, $customerGroup)
    {
        $categories = $product->getCategoryIds();
        $categoryCheck = $this->helperConfig->getConfigButtonCategory($categories, $storeId, $customerGroup);
        if ($categoryCheck === 'enable') { //enable in category
            return true;
        } elseif ($categoryCheck === 'disable') { //disable in category
            return false;
        } else { //use global config
            return $this->isActiveGlobalConfig();
        }
    }

    /**
     * Is active Product Config
     * @param ProductInterface $product
     * @param int $customerGroup
     * @return bool
     */
    private function isActiveProductConfig($product, $customerGroup)
    {
        if ($product->getQuoteCusGroup() == '') {
            return false;
        }
        $productConfigCustomerGroup = explode(',', $product->getQuoteCusGroup());
        if (in_array($customerGroup, $productConfigCustomerGroup)) {
            return true;
        }
        return false;
    }

    /**
     * Is active Global Config
     *
     * @return bool
     */
    private function isActiveGlobalConfig()
    {
        if ($this->getQuotable()) {
            if ($this->getQuotable() == 2) {
                $customerGroup = $this->getCustomerGroupId();
                if (in_array($customerGroup, $this->getApplyForCustomers())) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * To array function
     *
     * @param string $string
     * @return array
     */
    public function toArray($string)
    {
        if ($string) {
            $string = str_replace(' ', '', $string);
            $array = explode(',', $string);
            $newArray = array_filter($array, function ($value) {
                return $value !== '';
            });
            return $newArray;
        }
        return [];
    }

    /**
     * Get Currency Symbol
     *
     * @param int $store
     * @return string
     */
    public function getCurrentCurrencySymbol($store = null)
    {
        if ($store) {
            $currency = $this->currencyFactory->create()->load($store->getCurrentCurrencyCode());
            return $currency->getCurrencySymbol();
        } else {
            return $this->priceCurrency->getCurrency()->getCurrencySymbol();
        }
    }

    /**
     * Generate new random token
     *
     * @param int $length
     * @return string
     */
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Retrieve format price
     *
     * @param $value
     * @param int|null $storeId
     * @param null|string $currency
     * @return float
     */
    public function formatPrice($value, $storeId = null, $currency = null)
    {
        return $this->priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $storeId,
            $currency
        );
    }

    /**
     * Retrieve formated price include symbol
     *
     * @param float $value
     * @return float|string
     */
    public function formatCurrencyIncludeSymbol($value)
    {
        return $this->pricingHelper->currency($value, true, false);
    }

    /**
     * Retrieve formated price exclude symbol
     *
     * @param float $value
     * @return float|string
     */
    public function formatCurrencyExcludeSymbol($value)
    {
        return $this->pricingHelper->currency($value, false, false);
    }

    /**
     * Get customer name
     *
     * @param int $customerId
     * @return string|null
     */
    public function getCustomerName($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $exception) {
            return null;
        }
        return $this->customerHelper->getCustomerName($customer);
    }

    /**
     * Get Customer By Id
     *
     * @param int $customerId
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerById($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Return Required Address Config
     *
     * @param int $store
     * @return bool
     */
    public function isRequiredAddress($store = null)
    {
        if ($this->customerSession->create()->isLoggedIn()) {
            return $this->scopeConfig->isSetFlag(
                self::PATH_REQUEST4QUOTE_SHIPPING_REQUIRED,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        return false;

    }

    /**
     * Return Pending Request quote
     *
     * @return string
     */
    public function returnPendingStatus()
    {
        return Status::STATE_PENDING;
    }

    /**
     * If active theme is smartwave porto
     *
     * @return bool
     */
    public function isPortoThemeActive()
    {
        return $this->design->getDesignTheme()->getCode() === "Smartwave/porto";
    }

    /**
     * Get Json Serializer
     *
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function getJsonSerializer()
    {
        return $this->jsonSerializer;
    }

    /**
     * If module configurable grid view is enabled
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isConfigurableGridModEnabled()
    {
        if ($this->isModuleOutputEnabled('Bss_ConfiguableGridView')) {
            if ($this->scopeConfig->getValue(
                self::BSS_CONFIGURABLE_GRID_VIEW_ENABLE_CONFIG_XML_PATH,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getCode()
            )) {
                $disabledCustomerGroup = $this->scopeConfig->getValue(
                    self::BSS_CONFIGURABLE_GRID_VIEW_DISABLED_CUSTOMER_GROUP,
                    ScopeInterface::SCOPE_STORE,
                    $this->storeManager->getStore()->getCode()
                );
                if ($disabledCustomerGroup == '') {
                    return true;
                }
                $disabledCustomerGroup = explode(',', $disabledCustomerGroup);
                if (!in_array($this->customerSession->create()->getCustomerGroupId(), $disabledCustomerGroup)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get customer id current
     *
     * @return mixed
     */
    public function getCustomerIdCurrent()
    {
        return $this->customerSession->create()->getCustomer()->getId();
    }
    /**
     * Check install module company account
     *
     * @return bool
     */
    public function isInstallCompanyAccount()
    {
        return $this->_moduleManager->isEnabled('Bss_CompanyAccount');
    }

    /**
     * Check enable module company account
     *
     * @param null|string $website
     * @return bool
     * @throws LocalizedException
     */
    public function isEnableCompanyAccount($website = null)
    {
        if ($website === null) {
            $website = $this->storeManager->getWebsite()->getId();
        }
        $configEnable = (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_COMPANY_ACCOUNT,
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
        if ($configEnable && $this->isInstallCompanyAccount()) {
            return true;
        }
        return false;
    }

    /**
     * Get Quote id
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $requestQuote
     * @return mixed
     */
    public function getQuoteId($requestQuote)
    {
        $quoteId = $requestQuote->getQuoteId();
        $backendQuoteId = $requestQuote->getBackendQuoteId();
        if ($backendQuoteId) {
            return $backendQuoteId;
        }
        return $quoteId;
    }

    /**
     * Check install module Sales Rep
     *
     * @return bool
     */
    public function isInstallSalesRep()
    {
        return $this->_moduleManager->isEnabled('Bss_SalesRep');
    }
    /**
     * Module Enable Sales Rep
     *
     * @return bool
     */
    public function isEnableSalesRep()
    {
        $installSalesRep = $this->isInstallSalesRep();
        $configSalesRep = $this->scopeConfig->isSetFlag(
            self::PATH_SALES_REP_ENABLED,
            ScopeInterface::SCOPE_STORE,
            0
        );
        if ($installSalesRep && $configSalesRep) {
            return true;
        }
        return false;
    }

    /**
     * Apply normalization filter to item qty value
     *
     * @param int $itemQty
     * @return int|array
     */
    public function normalize($itemQty)
    {
        if ($itemQty) {
            $filter = new \Magento\Framework\Filter\LocalizedToNormalized(
                ['locale' => $this->resolver->getLocale()]
            );
            return $filter->filter((string)$itemQty);
        }
        return $itemQty;
    }
}
