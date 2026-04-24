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
namespace Bss\QuoteExtension\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class UnsetAllObserver
 *
 * @package Bss\QuoteExtension\Observer
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UnsetAllObserver implements ObserverInterface
{
    /**
     * @var \Bss\QuoteExtension\Model\Session
     */
    protected $quoteSession;

    /**
     * @param \Bss\QuoteExtension\Model\Session $quoteSession
     * @codeCoverageIgnore
     */
    public function __construct(\Bss\QuoteExtension\Model\Session $quoteSession)
    {
        $this->quoteSession = $quoteSession;
    }

    /**
     * Clear current request Quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->quoteSession->clearQuoteExtension()->clearStorage();
    }
}
