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
namespace Bss\QuoteExtension\Block\QuoteExtension;

/**
 * Class Tax
 *
 * Tax totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension
 */
class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * Tax constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Bss\QuoteExtension\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $taxConfig, $data);
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        /** @var $parent \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals */
        $parent = $this->getParentBlock();
        $this->_order = $parent->getQuoteExtension();
        $this->_source = $parent->getSource();

        $store = $this->getStore();
        $allowTax = $this->_source->getShippingAddress()->getTaxAmount() > 0 ||
            $this->_config->displaySalesZeroTax($store);
        $grandTotal = (double)$this->_source->getGrandTotal();
        if (!$grandTotal || $allowTax && !$this->_config->displaySalesTaxWithGrandTotal($store)) {
            $this->_addTax();
        }

        $this->_initSubtotal();
        $this->_initShipping();
        $this->_initDiscount();
        $this->_initGrandTotal();
        return $this;
    }

    /**
     * Format Price
     *
     * @param float $price
     * @param int $storeId
     * @return float|string
     */
    public function formatPrice($price, $storeId)
    {
        return $this->helper->formatPrice($price, $storeId, $this->_order->getQuoteCurrencyCode());
    }

    /**
     * Set shipping address to source before execute methods.
     *
     * @return $this
     */
    protected function _initShipping()
    {
        $source = $this->_source;
        $this->_source = $source->getShippingAddress();
        parent::_initShipping();
        $this->_source = $source;
        return $this;
    }

    /**
     * Set shipping address to source before execute methods.
     *
     * @return $this
     */
    protected function _initSubtotal()
    {
        $source = $this->_source;
        $this->_source = $source->getShippingAddress();
        parent::_initSubtotal();
        $this->_source = $source;
        return $this;
    }
}
