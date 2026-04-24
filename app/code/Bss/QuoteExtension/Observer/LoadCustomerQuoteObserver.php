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
 * Class LoadCustomerQuoteObserver
 *
 * @package Bss\QuoteExtension\Observer
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class LoadCustomerQuoteObserver implements ObserverInterface
{
    /**
     * @var \Bss\QuoteExtension\Model\Session
     */
    protected $quoteSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Bss\QuoteExtension\Model\Session $quoteSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @codeCoverageIgnore
     */
    public function __construct(
        \Bss\QuoteExtension\Model\Session $quoteSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->quoteSession = $quoteSession;
        $this->messageManager = $messageManager;
    }

    /**
     * Load customer by request quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->quoteSession->loadCustomerQuoteExtension();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Load customer quote extension error'));
        }
    }
}
