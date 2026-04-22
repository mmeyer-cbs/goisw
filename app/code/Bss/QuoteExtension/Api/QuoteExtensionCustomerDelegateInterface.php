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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Api;

/**
 * Delegate related to quote extension customers operations to Customer module.
 */
interface QuoteExtensionCustomerDelegateInterface
{
    /**
     * Redirect to Customer module new-account page to finish creating customer based on quote_extension, quote data.
     *
     * @param int $quoteId
     * @param int $quoteExtensionId
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function delegateNew(int $quoteId, $quoteExtensionId);
}
