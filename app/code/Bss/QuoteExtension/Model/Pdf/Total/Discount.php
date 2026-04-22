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
namespace Bss\QuoteExtension\Model\Pdf\Total;

/**
 * Class Discount
 *
 * @package Bss\QuoteExtension\Model\Pdf\Total
 */
class Discount extends DefaultTotal
{
    /**
     * Get Total amount from source
     *
     * @return float
     */
    public function getAmount()
    {
        $amount = 0;
        foreach ($this->getQuote()->getAllItems() as $item) {
            $amount += $item->getDiscountAmount();
        }
        return $amount;
    }
}
