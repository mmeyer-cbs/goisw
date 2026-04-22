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

use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ManageQuote;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PlaceOrder
 *
 * @package Bss\QuoteExtension\Observer
 */
class PlaceOrder implements ObserverInterface
{
    /**
     * @var ManageQuote
     */
    protected $manageQuote;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * PlaceOrder constructor.
     *
     * @param ManageQuote $manageQuote
     * @param LoggerInterface $logger
     */
    public function __construct(
        ManageQuote $manageQuote,
        LoggerInterface $logger
    ) {
        $this->manageQuote = $manageQuote;
        $this->logger = $logger;
    }

    /**
     * Execute Function. Set Status For Request quote ordered
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getQuote();
        if ($quote->getData('quote_extension')) {
            try {
                $this->manageQuote->load($quote->getId(), 'quote_id');

                if ($this->manageQuote->getId() && $this->manageQuote->getQuoteId()== $quote->getId()) {
                    $this->manageQuote->setData('status', Status::STATE_ORDERED);
                    $this->manageQuote->setData('token', '');
                    $this->manageQuote->save();
                } else {
                    $this->manageQuote->load($quote->getId(), 'backend_quote_id');

                    if ($this->manageQuote->getId() && $this->manageQuote->getBackendQuoteId()== $quote->getId()) {
                        $this->manageQuote->setData('status', Status::STATE_ORDERED);
                        $this->manageQuote->setData('token', '');
                        $this->manageQuote->save();
                    }
                }
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
            }
        }
    }
}
