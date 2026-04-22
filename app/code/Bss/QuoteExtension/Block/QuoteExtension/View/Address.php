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
namespace Bss\QuoteExtension\Block\QuoteExtension\View;

use Bss\QuoteExtension\Block\QuoteExtension\Submit\LayoutProcessor;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * Class Address
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension\View
 */
class Address extends \Bss\QuoteExtension\Block\QuoteExtension\AbstractQuoteExtension
{
    /**
     * Shipping rates
     *
     * @var array
     */
    protected $rates;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Bss\QuoteExtension\Block\QuoteExtension\Submit\LayoutProcessor
     */
    protected $layoutProcessor;

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\Address
     */
    protected $helperAddress;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Address constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param LayoutProcessor $layoutProcessor
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\Address $helperAddress
     * @param PriceCurrencyInterface $priceCurrency
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        LayoutProcessor $layoutProcessor,
        \Bss\QuoteExtension\Helper\QuoteExtension\Address $helperAddress,
        PriceCurrencyInterface $priceCurrency,
        Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->configProvider = $configProvider;
        $this->layoutProcessor = $layoutProcessor;
        $this->helperAddress = $helperAddress;
        $this->priceCurrency = $priceCurrency;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get js Layout
     */
    public function getJsLayout()
    {
        $this->jsLayout = $this->layoutProcessor->process($this->jsLayout);

        return $this->helperAddress->jsonEncodeDataConfig($this->jsLayout);
    }

    /**
     * Get active quote
     */
    public function getQuoteExtension()
    {
        if (null === $this->quote) {
            $this->quote = $this->coreRegistry->registry('current_quote');
        }
        return $this->quote;
    }

    /**
     * Retrieve current order model instance
     */
    public function getRequestQuote()
    {
        return $this->coreRegistry->registry('current_quote_extension');
    }

    /**
     * Can Submit Quote
     *
     * @return bool
     */
    public function canSubmitQuote()
    {
        $disableResubmit = $this->helperAddress->disableResubmit();
        $status = $this->getRequestQuote()->getStatus();
        if (!$disableResubmit) {
            $statusCanEdit = [
                Status::STATE_UPDATED,
                Status::STATE_REJECTED,
                Status::STATE_EXPIRED
            ];
        } else {
            $statusCanEdit = [
                Status::STATE_UPDATED
            ];
        }
        return in_array($status, $statusCanEdit);
    }

    /**
     * @param $type
     * @return string|null
     */
    public function getFormattedAddress($type)
    {
        return $this->helperAddress->formatAddress($type, $this->getQuoteExtension());
    }

    /**
     * Retrieve current selected shipping method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getQuoteExtension()->getShippingAddress()->getShippingMethod();
    }

    /**
     * Check activity of method by code
     *
     * @param string $code
     * @return bool
     */
    public function isMethodActive($code)
    {
        return $code === $this->getShippingMethod();
    }

    /**
     * Retrieve rate of active shipping method
     */
    public function getActiveMethodRate()
    {
        $rates = $this->getShippingRates();
        if (is_array($rates)) {
            foreach ($rates as $group) {
                foreach ($group as $rate) {
                    if ($rate->getCode() == $this->getShippingMethod()) {
                        return $rate;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get shipping price
     *
     * @param float $price
     * @param bool $flag
     * @return float
     */
    public function getShippingPrice($price, $flag)
    {
        return $this->priceCurrency->convertAndFormat(
            $this->helperAddress->getTaxHelper()->getShippingPrice(
                $price,
                $flag,
                $this->getQuoteExtension()->getShippingAddress(),
                null,
                $this->getQuoteExtension()->getStore()
            ),
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuoteExtension()->getStore()
        );
    }

    /**
     * Retrieve carrier name from store configuration
     *
     * @param string $carrierCode
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getQuoteExtension()->getStore()->getId()
        )
        ) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getShippingRates()
    {
        if (empty($this->rates)) {
            $this->rates = $this->getQuoteExtension()->getShippingAddress()->getGroupedAllShippingRates();
        }
        return $this->rates;
    }

    /**
     * get Tax Class Helper
     */
    public function getTaxHelper()
    {
        return $this->helperAddress->getTaxHelper();
    }

    /**
     * Return checkout config for quote extension
     */
    public function getCheckoutConfig()
    {
        $output = $this->configProvider->getConfig();

        $output['quoteData'] = $this->getQuoteData();
        $output['quoteItemData'] = $this->getQuoteItemData();
        $output['selectedShippingMethod'] = $this->getSelectedShippingMethod();
        $output['storeCode'] = $this->getStoreCode();
        $output['staticBaseUrl'] = $this->getStaticBaseUrl();

        if (isset($output['checkoutUrl'])) {
            $output['checkoutUrl'] = $this->getUrl('quoteextension/quote/');
        }
        if (isset($output['isGuestCheckoutAllowed'])) {
            $output['isGuestCheckoutAllowed'] = false;
        }
        if (isset($output['defaultSuccessPageUrl'])) {
            $output['defaultSuccessPageUrl'] = $this->getUrl('quoteextension/quote/success');
        }
        $output['isRequiredAddress'] = $this->isRequiredAddress();
        return $output;
    }

    /**
     * Get quote Data
     *
     * @return array
     */
    protected function getQuoteData()
    {
        $quoteData = [];
        if ($this->getQuoteExtension()->getId()) {
            $quoteData = $this->getQuoteExtension()->toArray();
            $quoteData['is_virtual'] = $this->getQuoteExtension()->getIsVirtual();
        }
        return $quoteData;
    }

    /**
     * Get Quote item data
     */
    protected function getQuoteItemData()
    {
        $quoteItemData = [];
        $quoteId = $this->getQuoteExtension()->getId();
        if ($quoteId) {
            $quoteItems = $this->helperAddress->getListItemsById($quoteId);
            foreach ($quoteItems as $index => $quoteItem) {
                $quoteItemData[$index] = $quoteItem->toArray();
                $quoteItemData[$index]['options'] = $this->getFormattedOptionValue($quoteItem);
            }
        }
        return $quoteItemData;
    }

    /**
     * Get shipping methods
     *
     * @return array|null
     */
    protected function getSelectedShippingMethod()
    {
        $shippingMethodData = null;
        try {
            $quoteId = $this->getQuoteExtension()->getId();
            $shippingMethod = $this->helperAddress->getShippindMethods($quoteId);
            if ($shippingMethod) {
                $shippingMethodData = $shippingMethod->__toArray();
            }
        } catch (\Exception $exception) {
            $shippingMethodData = null;
        }
        return $shippingMethodData;
    }

    /**
     * Retrieve store code
     *
     * @return string
     */
    protected function getStoreCode()
    {
        return $this->getQuoteExtension()->getStore()->getCode();
    }

    /**
     * Get Static Base Url
     *
     * @return string
     */
    protected function getStaticBaseUrl()
    {
        return $this->getQuoteExtension()->getStore()->getBaseUrl(UrlInterface::URL_TYPE_STATIC);
    }

    /**
     * Shipping Address is required\
     *
     * @return bool
     */
    public function isRequiredAddress()
    {
        return $this->helperAddress->isRequiredAddress();
    }
}
