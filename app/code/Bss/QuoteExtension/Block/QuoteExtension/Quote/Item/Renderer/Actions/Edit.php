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
namespace Bss\QuoteExtension\Block\QuoteExtension\Quote\Item\Renderer\Actions;

/**
 * Class Edit
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension\Quote\Item\Renderer\Actions
 */
class Edit extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic
{
    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        return $this->getUrl(
            'quoteextension/quote/configure',
            [
                'id' => $this->getItem()->getId(),
                'product_id' => $this->getItem()->getProduct()->getId(),
            ]
        );
    }
}
