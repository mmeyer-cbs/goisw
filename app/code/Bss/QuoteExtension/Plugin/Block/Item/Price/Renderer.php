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
namespace Bss\QuoteExtension\Plugin\Block\Item\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

/**
 * Class Renderer
 *
 * @package Bss\QuoteExtension\Plugin\Block\Item\Price
 */
class Renderer
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Renderer constructor.
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\Tax\Block\Item\Price\Renderer $subject
     * @param \Closure $proceed
     * @param int $price
     * @return mixed
     */
    public function aroundFormatPrice(
        \Magento\Tax\Block\Item\Price\Renderer $subject,
        \Closure $proceed,
        $price
    ) {
        $item = $subject->getItem();
        if ($item instanceof QuoteItem) {
            $quote = $item->getQuote();
            if ($quote->getQuoteExtension()) {
                return $this->priceCurrency->format(
                    $price,
                    true,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $item->getStore(),
                    $quote->getQuoteCurrencyCode()
                );
            }
        }
        $result = $proceed($price);
        return $result;
    }

    /**
     * @param \Magento\Tax\Block\Item\Price\Renderer $subject
     * @param \Closure $proceed
     * @return float|mixed
     */
    public function aroundGetItemDisplayPriceExclTax(
        \Magento\Tax\Block\Item\Price\Renderer $subject,
        \Closure $proceed
    ) {
        $item = $subject->getItem();
        if ($item instanceof QuoteItem) {
            $quote = $item->getQuote();
            if ($quote->getQuoteExtension()) {
                return $this->priceCurrency->convert(
                    $item->getPrice(),
                    $item->getStore(),
                    $quote->getQuoteCurrencyCode()
                );
            }
        }
        $result = $proceed();
        return $result;
    }
}
