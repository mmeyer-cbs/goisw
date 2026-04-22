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
namespace Bss\QuoteExtension\Model;

/**
 * Class Quote
 */
class QuoteSuccess
{
    /**
     * @var ManageQuote|null
     */
    public $lastQuote;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var ManageQuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ManageQuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Bss\QuoteExtension\Model\ManageQuoteFactory $quoteFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory    = $quoteFactory;
    }

    /**
     * Get last quote after submit quote extension
     *
     * @return ManageQuote
     */
    public function getLastQuote()
    {
        if (!$this->lastQuote) {
            $lastQuoteId = $this->getLastQuoteIdExtension();
            if ($lastQuoteId) {
                $this->lastQuote = $this->quoteFactory->create()->load($lastQuoteId);
            }
        }
        return $this->lastQuote;
    }

    /**
     * Get last quote id extension
     *
     * @return int|null
     */
    public function getLastQuoteIdExtension()
    {
        return $this->checkoutSession->getLastManaQuoteExtensionId();
    }
}
