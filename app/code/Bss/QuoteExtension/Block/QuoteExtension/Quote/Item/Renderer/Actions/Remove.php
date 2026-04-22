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

use Bss\QuoteExtension\Helper\QuoteExtensionCart;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\View\Element\Template;

/**
 * Class Remove
 */
class Remove extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic
{
    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @param Template\Context $context
     * @param QuoteExtensionCart $cartHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        QuoteExtensionCart $cartHelper,
        array $data = []
    ) {
        $this->cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get delete item POST JSON
     *
     * @return string
     */
    public function getDeletePostJson()
    {
        return $this->cartHelper->getDeletePostJson($this->getItem());
    }
}
