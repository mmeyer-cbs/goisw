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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Model;

use Exception;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Pricing\Helper\Data as PricingHelperData;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Currency
 */
class Currency extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PricingHelperData
     */
    protected $priceHelperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * Currency constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param PricingHelperData $priceHelperData
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param CurrencyFactory $currency
     * @param Context $context
     */
    public function __construct(
        FormatInterface $localeFormat,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        PricingHelperData $priceHelperData,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        CurrencyFactory $currency,
        Context $context
    ) {
        $this->localeFormat = $localeFormat;
        $this->scopeConfig = $scopeConfig;
        $this->priceHelperData = $priceHelperData;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->currency = $currency;
        parent::__construct($context);
    }

    /**
     * Get currency by currency code.
     *
     * @param string|null $currencyCode
     * @return string|\Magento\Directory\Model\Currency
     */
    public function getCurrencyByCode($currencyCode)
    {
        try {
            if (!$currencyCode) {
                return $this->storeManager->getStore()->getBaseCurrency();
            }
            return $this->currency->create()->load($currencyCode);
        } catch (Exception $exception) {
            $this->_logger->critical($exception->getMessage());
        }
        return "";
    }

    /**
     * Prepare formatted price.
     *
     * @param float|int $price
     * @param AbstractModel|string|null $currency
     * @return string
     */
    public function formatPrice($price, $currency)
    {
        return $this->priceCurrency->format($price, false, PriceCurrencyInterface::DEFAULT_PRECISION, null, $currency);
    }

    /**
     * Convert currency
     *
     * @param float $value
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @return float|int|mixed|null
     */
    public function convertCurrency($value, $fromCurrencyCode, $toCurrencyCode)
    {
        if (!$fromCurrencyCode) {
            $fromCurrencyCode =  $this->getCurrencyCodeByWebsite();
        }
        if (!$toCurrencyCode) {
            $toCurrencyCode =  $this->getCurrencyCodeByWebsite();
        }
        if ($fromCurrencyCode != $toCurrencyCode) {
            try {
                $fromCurrency = $this->getCurrencyByCode($fromCurrencyCode);
                $toCurrency = $this->getCurrencyByCode($toCurrencyCode);
                $value = $fromCurrency->convert($value, $toCurrency);
            } catch (Exception $e) {
                $value = null;
            }
        }
        return $value;
    }

    /**
     * Get currency code by website id
     *
     * @param int|null $websiteId
     * @return null|string
     */
    public function getCurrencyCodeByWebsite($websiteId = null)
    {
        try {
            if (!$websiteId) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            return $this->storeManager->getWebsite($websiteId)->getBaseCurrencyCode();
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
        }
        return null;
    }

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  float|string
     */
    public function currency($value, $format = true, $includeContainer = true)
    {
        return $this->priceHelperData->currency($value, $format, $includeContainer);
    }

    /**
     * Get currency code store view
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getCurrencyCodeStoreView($storeId = null)
    {
        return $this->priceCurrency->getCurrency($storeId)->getCurrencyCode();
    }

    /**
     * Display available credit by store view
     *
     * @param string $availableCredit
     * @param string $currencyCode
     * @return string
     */
    public function displayStoreView($availableCredit, $currencyCode)
    {
        if (!$currencyCode) {
            $currencyCode = $this->getCurrencyCodeByWebsite();
        }
        $currencySymbol = $this->getCurrencyCodeStoreView();
        $availableCredit = $this->convertCurrency($availableCredit, $currencyCode, $currencySymbol);
        return $this->formatPrice($availableCredit, $currencySymbol);
    }

    /**
     * @param $price
     * @return float
     */
    public function round($price)
    {
        return $this->priceCurrency->round($price);
    }

    /**
     * Convert and format price currency
     *
     * @param float $price
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     */
    public function convertAndFormatPriceCurrency($price, $fromCurrencyCode, $toCurrencyCode)
    {
        $price = $this->convertCurrency($price, $fromCurrencyCode, $toCurrencyCode);
        return $this->formatPrice($price, $toCurrencyCode);
    }

    /**
     * Convert amount input by currency
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return  array
     */
    public function convertAmountInput($quote)
    {
        $balanceInput = $quote->getBssStorecreditAmountInput();
        if ($balanceInput) {
            $storeCreditCurrencyCode = $quote->getStoreCreditCurrencyCode() ? $quote->getStoreCreditCurrencyCode() : $quote->getQuoteCurrencyCode();
            $balanceInput = $this->convertCurrency($balanceInput, $storeCreditCurrencyCode, $quote->getQuoteCurrencyCode());
            $baseBalanceInput = $this->convertCurrency($balanceInput, $storeCreditCurrencyCode, $quote->getBaseCurrencyCode());
        } else {
            $baseBalanceInput = $quote->getBaseBssStorecreditAmountInput();
            $balanceInput = $this->convertCurrency($baseBalanceInput, $quote->getBaseCurrencyCode(), $quote->getQuoteCurrencyCode());
        }
        return [$baseBalanceInput, $balanceInput];
    }

    /**
     * Get credit currency code: handle data if installed module version 1.1.2
     *
     * @param string $creditCurrencyCode
     * @param int|null $webiteId
     */
    public function getCreditCurrencyCode($creditCurrencyCode = null, $webiteId = null)
    {
        return $creditCurrencyCode ? $creditCurrencyCode : $this->getCurrencyCodeByWebsite($webiteId);
    }

    /**
     * Get locale by website id
     *
     * @param null|int $webisteId
     * @return string
     */
    public function getLocaleByWebsiteId($webisteId) {
        return $this->scopeConfig->getValue(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $webisteId
        );
    }

    /**
     * Get Price Format
     *
     * @param int|null $websiteId
     * @return array
     */
    public function getPriceFormat($websiteId = null)
    {
        return $this->localeFormat->getPriceFormat($this->getLocaleByWebsiteId($websiteId), $this->getCurrencyCodeByWebsite($websiteId));
    }
}
