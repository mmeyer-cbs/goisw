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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Helper;

use Bss\CustomPricing\Model\Config\Source\PriceTypeOption;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Setup\Console\InputValidationException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Helper data for module
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLE = 'bss_custom_pricing/general/enable';
    const XML_PATH_APPLY_NORMAL_SPECIAL_PRICE = 'bss_custom_pricing/general/apply_normal_special_price';
    const XML_PATH_APPLY_NORMAL_TIER_PRICE = 'bss_custom_pricing/general/apply_normal_tier_price';
    const XML_PATH_PRICE_SCOPE = 'catalog/price/scope';
    const XML_PATH_SHARE_ACCOUNT = 'customer/account_share/scope';
    const PRICE_SCOPE_GLOBAL = 0;
    const PER_WEBSITE = 1;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTyleList;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        parent::__construct($context);
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
        $this->backendSession = $backendSession;
        $this->cacheTyleList = $cacheTypeList;
    }

    /**
     * Retrieve backend session object
     *
     * @return \Magento\Backend\Model\Session
     */
    public function getBackendSession()
    {
        return $this->backendSession;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_getRequest();
    }

    /**
     * Retrieve store base currency code
     *
     * @param string $website
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBaseCurrencyCode($website)
    {
        $configValue = $this->getConfig(self::XML_PATH_PRICE_SCOPE);
        if ($configValue == self::PRICE_SCOPE_GLOBAL) {
            return $this->scopeConfig->getValue(
                Currency::XML_PATH_CURRENCY_BASE,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        }
        return $this->getConfig(Currency::XML_PATH_CURRENCY_BASE, $website);
    }

    /**
     * Get scope config
     *
     * @param string $path
     * @param string|null $scopeCode
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig($path, $scopeCode = null)
    {
        if (!$scopeCode) {
            $scopeCode = $this->storeManager->getWebsite()->getCode();
        }
        $data = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeCode
        );
        if ($data === null) {
            $data = $this->scopeConfig->getValue($path);
        }
        return $data === false ? null : $data;
    }

    /**
     * Get currency symbol by code
     *
     * @param string $websiteId
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrencySymbol($websiteId)
    {
        $currencyCode = $this->getBaseCurrencyCode($websiteId);
        try {
            return $this->currencyFactory->create()
                ->load($currencyCode)
                ->getCurrencySymbol();
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * Apply Normal Special Price to product that has Custom Price
     *
     * @param null|int $scopeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyNormalSpecialPrice($scopeCode = null)
    {
        if (!$scopeCode) {
            $scopeCode = $this->storeManager->getWebsite()->getCode();
        }
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_APPLY_NORMAL_SPECIAL_PRICE,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeCode
        );
    }

    /**
     * Get is apply normal tier price config
     *
     * @param null|string $scopeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyNormalTierPrice($scopeCode = null)
    {
        return (bool) $this->getConfig(self::XML_PATH_APPLY_NORMAL_TIER_PRICE, $scopeCode);
    }

    /**
     * Enable module.
     *
     * @param null|int $scopeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEnabled($scopeCode = null)
    {
        if (!$scopeCode) {
            $scopeCode = $this->storeManager->getWebsite()->getCode();
        }
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeCode
        );
    }

    /**
     * Some awesome cleared describe the code above
     *
     * @param int $priceType
     * @param float $originPrice
     * @param float $customPrice
     *
     * @return float
     * @throws InputValidationException
     */
    public function prepareCustomPrice($priceType, $originPrice, $customPrice)
    {
        if (!$priceType) {
            $priceType = PriceTypeOption::ABSOLUTE_PRICE;
        }
        switch ($priceType) {
            case PriceTypeOption::ABSOLUTE_PRICE:
                return $customPrice;
            case PriceTypeOption::INCREASE_FIXED_PRICE:
                return $originPrice + $customPrice;
            case PriceTypeOption::DECREASE_FIXED_PRICE:
                return $customPrice > $originPrice ?
                    0 :
                    $originPrice - $customPrice;
            case PriceTypeOption::INCREASE_PERCENT_PRICE:
                return $originPrice * (100 + $customPrice)/100;
            case PriceTypeOption::DECREASE_PERCENT_PRICE:
                $decreasePrice = $originPrice - ($originPrice * ($customPrice/100));
                if ($decreasePrice > 0) {
                    return $decreasePrice;
                }
                return 0;
            default:
                throw new InputValidationException(
                    __("'price type' param missing on you request! Please refresh and try again.")
                );
        }
    }

    /**
     * Is scope storage customer per website or global
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isScopeCustomerPerWebsite()
    {
        $shareAccount = $this->getConfig(self::XML_PATH_SHARE_ACCOUNT);
        if ($shareAccount == self::PER_WEBSITE) {
            return true;
        }
        return false;
    }

    /**
     * Need clear cache after save rule
     * @since 1.0.7
     */
    public function markInvalidateCache()
    {
        $this->cacheTyleList->invalidate(
            [
                \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER,
                \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER
            ]
        );
    }
}
