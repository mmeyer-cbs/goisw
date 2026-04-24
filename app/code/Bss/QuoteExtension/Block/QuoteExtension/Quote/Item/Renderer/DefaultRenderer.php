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
namespace Bss\QuoteExtension\Block\QuoteExtension\Quote\Item\Renderer;

use Bss\QuoteExtension\Model\Config\Source\Status;

/**
 * Quote item render block
 */
class DefaultRenderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * Quotation Helper
     *
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * DefaultRenderer constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Bss\QuoteExtension\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        array $data
    ) {
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $string, $productOptionFactory, $data);
    }

    /**
     * Has Optional Products
     *
     * @return bool
     */
    public function hasOptionalProducts()
    {
        return $this->getParentBlock()
        && $this->getParentBlock()->getParentBlock()
        && $this->getParentBlock()->getParentBlock()->hasOptionalProducts();
    }

    /**
     * Check quote can accept
     *
     * @return boolean
     */
    public function canAccept()
    {
        return $this->helper->canAccept($this->getQuote());
    }

    /**
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getQuoteItem()->getQuoteExtension();
    }

    /**
     * Get the quote item
     *
     * @return array|null
     */
    public function getQuoteItem()
    {
        if ($this->getItem() instanceof \Magento\Quote\Model\Quote\Item) {
            return $this->getItem();
        } else {
            return $this->getItem()->getQuoteItem();
        }
    }

    /**
     * Get delete item url
     *
     * @param int $itemId
     * @return string
     */
    public function getDeleteUrl($itemId)
    {
        return $this->getUrl(
            'quoteextension/quote/delete',
            [
                'id' => $itemId,
                'quote_id' => $this->getQuote()->getId()
            ]
        );
    }

    /**
     * Return show comment
     *
     * @return array
     */
    public function canShowComment()
    {
        return $this->helper->isEnableQuoteItemsComment();
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getRequestQuote()
    {
        return $this->coreRegistry->registry('current_quote_extension');
    }

    /**
     * Can submit quote
     *
     * @return bool
     */
    public function canSubmitQuote()
    {
        $status = $this->getRequestQuote()->getStatus();
        $statusCanEdit = [
            Status::STATE_UPDATED,
            Status::STATE_REJECTED,
            Status::STATE_EXPIRED
        ];
        return in_array($status, $statusCanEdit);
    }
}
