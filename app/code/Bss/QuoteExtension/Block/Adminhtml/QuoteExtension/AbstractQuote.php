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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension;

/**
 * Adminhtml quote abstract block
 *
 * Class AbstractQuote
 */
class AbstractQuote extends \Magento\Backend\Block\Widget
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Admin helper
     * @var \Magento\Sales\Helper\Admin
     */
    protected $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->adminHelper = $adminHelper;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Display price attribute
     *
     * @param string $code
     * @param bool $strong
     * @param string $separator
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function displayPriceAttribute($code, $strong = false, $separator = '<br/>')
    {
        return $this->adminHelper->displayPriceAttribute($this->getPriceDataObject(), $code, $strong, $separator);
    }

    /**
     * Get price data object
     *
     * @return \Bss\QuoteExtension\Model\Quote|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPriceDataObject()
    {
        $obj = $this->getData('price_data_object');
        if ($obj === null) {
            return $this->getQuote();
        }
        return $obj;
    }

    /**
     * Retrieve available quote
     *
     * @return \Bss\QuoteExtension\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote()
    {
        if ($mageQuote = $this->coreRegistry->registry('mage_quote')) {
            return $mageQuote;
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the quote instance right now.'));
    }

    /**
     * Retrieve quote totals block settings
     *
     * @return array
     */
    public function getQuoteTotalData()
    {
        return [];
    }

    /**
     * Retrieve quote info block settings
     *
     * @return array
     */
    public function getQuoteInfoData()
    {
        return [];
    }

    /**
     * Retrieve subtotal price include tax html formated content
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function displayShippingPriceInclTax($quote)
    {
        $shipping = $quote->getShippingInclTax();
        if ($shipping) {
            $baseShipping = $quote->getBaseShippingInclTax();
        } else {
            $shipping = $quote->getShippingAmount() + $quote->getShippingTaxAmount();
            $baseShipping = $quote->getBaseShippingAmount() + $quote->getBaseShippingTaxAmount();
        }
        return $this->displayPrices($baseShipping, $shipping, false, ' ');
    }

    /**
     * Display prices
     *
     * @param float $basePrice
     * @param float $price
     * @param bool $strong
     * @param string $separator
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function displayPrices($basePrice, $price, $strong = false, $separator = '<br/>')
    {
        return $this->adminHelper->displayPrices(
            $this->getPriceDataObject(),
            $basePrice,
            $price,
            $strong,
            $separator
        );
    }
}
