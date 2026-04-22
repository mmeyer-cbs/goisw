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
namespace Bss\QuoteExtension\Helper;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Class QuoteItems
 *
 * @package Bss\QuoteExtension\Helper
 */
class QuoteItems
{
    /**
     * Customer form factory
     *
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $customerFormFactory;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * Tax config
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * QuoteItems constructor.
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param Data $helperData
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $taxData
     */
    public function __construct(
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Bss\QuoteExtension\Helper\Data $helperData,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->customerFormFactory = $customerFormFactory;
        $this->addressMapper = $addressMapper;
        $this->localeCurrency = $localeCurrency;
        $this->helperData = $helperData;
        $this->taxConfig = $taxConfig;
        $this->taxData = $taxData;
    }

    /**
     * Get Address Form
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return \Magento\Customer\Model\Metadata\Form
     */
    public function getAddressForm($address)
    {
        $addressForm = $this->customerFormFactory->create(
            'customer_address',
            'adminhtml_customer_address',
            $this->addressMapper->toFlatArray($address)
        );
        return $addressForm;
    }

    /**
     * Get currency by currency code
     *
     * @param string $currencyCode
     * @return \Magento\Framework\Currency
     */
    public function getCurrency($currencyCode)
    {
        $currency = $this->localeCurrency->getCurrency($currencyCode);
        return $currency;
    }

    /**
     * Get Helper Data
     *
     * @return Data
     */
    public function getHelperData()
    {
        return $this->helperData;
    }

    /**
     * Return Final Price Code
     *
     * @return string
     */
    public function returnFinalPriceCode()
    {
        return FinalPrice::PRICE_CODE;
    }

    /**
     * Return disable product status value
     *
     * @return int
     */
    public function returnDisableStatus()
    {
        return ProductStatus::STATUS_DISABLED;
    }

    /**
     * Return Bundles Type
     *
     * @return string
     */
    public function returnBundlesType()
    {
        return \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;
    }

    /**
     * Display total Include tax by store
     *
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @return bool
     */
    public function displayTotalsIncludeTax($store)
    {
        $result = $this->taxConfig->displayCartSubtotalInclTax($store)
            || $this->taxConfig->displayCartSubtotalBoth($store);
        return $result;
    }

    /**
     * Display Price Include tax by store
     *
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    public function isPriceIncludesTax($store)
    {
        return $this->taxData->priceIncludesTax($store);
    }

    /**
     * Get Quote address
     *
     * @param object $quote
     * @return string
     */
    public function getQuoteAddress($quote)
    {
        if ($quote->isVirtual()) {
            return $quote->getBillingAddress();
        } else {
            return $quote->getShippingAddress();
        }
    }
}
