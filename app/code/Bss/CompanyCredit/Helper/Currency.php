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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Helper;

use Exception;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Pricing\Helper\Data as PricingHelperData;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class Currency extends AbstractHelper
{
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
     * @param PricingHelperData $priceHelperData
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param CurrencyFactory $currency
     * @param Context $context
     */
    public function __construct(
        PricingHelperData $priceHelperData,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        CurrencyFactory $currency,
        Context $context
    ) {
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
     * @return float|null
     */
    public function convertCurrency($value, $fromCurrencyCode, $toCurrencyCode)
    {
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
     * @return mixed
     */
    public function getCurrencyCodeStoreView()
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }
}
