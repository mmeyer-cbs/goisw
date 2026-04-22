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
namespace Bss\QuoteExtension\Plugin\QuoteExtension\Block\Cart\Item\Renderer;

use Bss\QuoteExtension\Block\Cart\Item\Renderer\Bundle;
use Bss\QuoteExtension\Block\Cart\Item\Renderer\Configurable;
use Bss\QuoteExtension\Block\Cart\Item\Renderer\Grouped;
use Bss\QuoteExtension\Model\ManageQuote;

/**
 * Class ProductType
 */
class ProductType
{
    /**
     * @var \Bss\QuoteExtension\Helper\Model
     */
    protected $helperModel;

    /**
     * Bundle constructor.
     * @param \Bss\QuoteExtension\Helper\Model $helperModel
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Model $helperModel
    ) {
        $this->helperModel = $helperModel;
    }

    /**
     * Get quote extension
     *
     * @param Bundle|Configurable|Grouped $subject
     * @param ManageQuote|null $result
     *
     * @return ManageQuote|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuoteExtension($subject, $result)
    {
        return $this->helperModel->getQuoteExtension();
    }
}
