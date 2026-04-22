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

use Bss\QuoteExtension\Model\Quote;

/**
 * Class AbstractQuoteExtension
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension
 */
class AbstractQuoteExtension extends \Magento\Framework\View\Element\Template
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * @var Quote|null
     */
    protected $quote = null;

    /**
     * @var array
     */
    protected $totals;

    /**
     * @var array
     */
    protected $itemRenders = [];

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $quoteExtensionSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->quoteExtensionSession = $quoteExtensionSession;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve renderer list
     *
     * @return bool|\Magento\Framework\View\Element\AbstractBlock|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getRendererList()
    {
        return $this->getRendererListName() ? $this->getLayout()->getBlock(
            $this->getRendererListName()
        ) : $this->getChildBlock(
            'renderer.list'
        );
    }

    /**
     * Retrieve item renderer block
     *
     * @param string|null $type
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemRenderer($type = null)
    {
        if ($type === null) {
            $type = self::DEFAULT_TYPE;
        }
        $rendererList = $this->_getRendererList();
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }
        $overriddenTemplates = $this->getOverriddenTemplates() ?: [];
        $template = isset($overriddenTemplates[$type]) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
        return $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    public function getQuoteExtension()
    {
        if (null === $this->quote) {
            $this->quote = $this->quoteExtensionSession->getQuoteExtension();
        }
        return $this->quote;
    }

    /**
     * Get all cart items
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getItems()
    {
        return $this->getQuoteExtension()->getAllVisibleItems();
    }

    /**
     * Get item row html
     *
     * @param object $item
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemHtml($item)
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

    /**
     * Get Totals
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getTotals()
    {
        return $this->getTotalsCache();
    }

    /**
     * Get Totals
     *
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->totals)) {
            if ($this->getQuoteExtension()->isVirtual()) {
                $this->totals = $this->getQuoteExtension()->getBillingAddress()->getTotals();
            } else {
                $this->totals = $this->getQuoteExtension()->getShippingAddress()->getTotals();
            }
        }
        return $this->totals;
    }
}
