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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Plugin\Adminhtml\Order\Create\Items;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid as ItemsGrid;

/**
 * Class Grid
 * @package Bss\StoreCredit\Plugin\Adminhtml\Order\Create\Items
 */
class Grid
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Plugin Get subtotal with discount
     *
     * @param ItemsGrid $subject
     * @param float $result
     * @return float
     */
    public function afterGetSubtotalWithDiscount(
        ItemsGrid $subject,
        $result
    ) {
        $balance = $subject->getQuoteAddress()->getBssStorecreditAmount();
        $amount = (float) $this->priceCurrency->round($balance);
        if ($amount && $result && $result >= $amount) {
            return $result - $amount;
        }
        return $result;
    }
}
