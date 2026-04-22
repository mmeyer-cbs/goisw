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
namespace Bss\QuoteExtension\Model\Type;

/**
 * Class Onepage
 *
 * @package Bss\QuoteExtension\Model\Type
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Onepage extends \Magento\Checkout\Model\Type\Onepage
{
    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * Quote object getter
     *
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The Quote does not exist.'));
        }
        return $this->_quote;
    }

    /**
     * Declare checkout quote instance
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Bss\QuoteExtension\Model\Type\Onepage
     * @codeCoverageIgnore
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }
}
