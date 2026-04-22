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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Helper;

use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Model
 *
 * @package Bss\QuoteExtension\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Model extends AbstractHelper
{
    const XML_PATH_ENABLED_HIDE_PRICE = 'bss_hide_price/general/enable';

    /**
     * @var CustomerQuoteExtension
     */
    protected $quoteExtension;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuote|null
     */
    protected $quoteExtensionSave = null;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuoteFactory
     */
    protected $quoteExtensionFactory;

    /**
     * Model constructor.
     * @param CustomerQuoteExtension $quoteExtension
     * @param \Bss\QuoteExtension\Model\ManageQuoteFactory $quoteExtensionFactory
     * @param Context $context
     */
    public function __construct(
        CustomerQuoteExtension $quoteExtension,
        \Bss\QuoteExtension\Model\ManageQuoteFactory $quoteExtensionFactory,
        Context $context
    ) {
        $this->quoteExtension = $quoteExtension;
        $this->quoteExtensionFactory = $quoteExtensionFactory;
        parent::__construct($context);
    }
    /**
     * Get quote extension
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote|null
     */
    public function getQuoteExtension()
    {
        $quoteId = ($this->_getRequest()->getParam('quote_id'));
        if ($quoteId) {
            $this->quoteExtensionSave = $this->quoteExtensionFactory->create()->load($quoteId);
        }
        return $this->quoteExtensionSave;
    }

    /**
     * Check can show subtotal
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canShowSubtotal()
    {
        if($this->enableBssHidePrice()) {
            foreach (array_reverse($this->quoteExtension->getQuote()->getAllItems()) as $item) {
                /* @var $item \Magento\Quote\Model\Quote\Item */
                if ($item->getProduct()->getCanShowPrice() === false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check enable module Bss_HidePrice
     *
     * @return bool
     */
    public function enableBssHidePrice() {
        return $this->_moduleManager->isEnabled("Bss_HidePrice") &&
            $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED_HIDE_PRICE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Quote Extension
     *
     * @return CustomerQuoteExtension
     */
    public function quoteExtension() {
        return $this->quoteExtension;
    }
}
