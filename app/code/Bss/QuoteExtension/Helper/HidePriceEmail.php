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

/**
 * Class HidePriceEmail
 *
 * @package Bss\QuoteExtension\Helper
 */
class HidePriceEmail extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param int $parentProductId
     * @param mixed $childProductSku
     * @param mixed $customerGroupId
     * @param mixed $storeId
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canShowPrice(
        $parentProductId,
        $childProductSku = false,
        $customerGroupId = false,
        $storeId = null,
        $quote = null
    ) {
        return true;
    }
}
