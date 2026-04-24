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
namespace Bss\QuoteExtension\Block\QuoteExtension\Quote\Item;

/**
 * Class Renderer
 */
class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * @var \Bss\QuoteExtension\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuote|null
     */
    protected $quoteExtension = null;

    /**
     * Renderer constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $messageInterpretationStrategy
     * @param \Bss\QuoteExtension\Model\QuoteFactory $quoteFactory
     * @param \Bss\QuoteExtension\Model\ManageQuoteFactory $quoteExtensionFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\QuoteExtension\Model\QuoteFactory $quoteFactory,
        \Bss\QuoteExtension\Model\ManageQuoteFactory $quoteExtensionFactory,

        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $messageInterpretationStrategy,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data
        );

        $this->quoteFactory = $quoteFactory;
        $quoteId = ($this->getRequest()->getParam('quote_id'));
        if ($quoteId) {
            $this->quoteExtension = $quoteExtensionFactory->create()->load($quoteId);
        }

    }

    /**
     * Get quote extension
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote|null
     */
    public function getQuoteExtension()
    {
        return $this->quoteExtension;
    }

    /**
     * Get delete item url
     *
     * @param int $tierItemId
     * @return string
     */
    public function getDeleteUrl($tierItemId)
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        if (!$quoteId) {
            return '';
        }

        return $this->getUrl('quoteextension/quote/delete', ['id' => $tierItemId, 'quote_id' => $quoteId]);
    }
}
