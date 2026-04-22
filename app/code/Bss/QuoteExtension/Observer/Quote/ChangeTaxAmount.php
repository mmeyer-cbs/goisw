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
namespace Bss\QuoteExtension\Observer\Quote;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ChangeTaxAmount
 *
 * @package Bss\QuoteExtension\Observer\Quote
 */
class ChangeTaxAmount implements ObserverInterface
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $helperTax;

    /**
     * ChangeTaxAmount constructor.
     *
     * @param \Magento\Tax\Helper\Data $helperTax
     */
    public function __construct(
        \Magento\Tax\Helper\Data $helperTax
    ) {
        $this->helperTax = $helperTax;
    }

    /**
     * Remove tax amount from quote if tax base on billing address
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $taxBaseOn = $this->helperTax->getTaxBasedOn();
        $quote = $observer->getQuote();
        if ($taxBaseOn == "billing" && $quote->getQuoteExtension()) {
            $total = $observer->getTotal();
            $taxAmount = $total->getTaxAmount();
            $total->setTaxAmount(0);
            $total->setBaseTaxAmount(0);
            $total->setGrandTotal((float)$total->getGrandTotal() - $taxAmount);
            $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() - $taxAmount);
            $total->setSubtotalInclTax((float)$total->getSubtotalInclTax() - $taxAmount);
            $total->setBaseSubtotalTotalInclTax((float)$total->getBaseSubtotalTotalInclTax() - $taxAmount);
        }
    }
}
