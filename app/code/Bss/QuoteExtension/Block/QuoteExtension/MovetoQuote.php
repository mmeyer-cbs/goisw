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
namespace Bss\QuoteExtension\Block\QuoteExtension;

/**
 * Button move all cart to quote
 */
class MovetoQuote extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * MovetoQuote constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\QuoteExtension\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check if quote extension visibility is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnable();
    }

    /**
     * Get url move quote
     *
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('quoteextension/quote/movetoQuote');
    }
}
