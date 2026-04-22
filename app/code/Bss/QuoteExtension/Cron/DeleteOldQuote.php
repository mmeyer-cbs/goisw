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
namespace Bss\QuoteExtension\Cron;

use Bss\QuoteExtension\Helper\Data;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as ManageQuoteFactory;

/**
 * Class DeleteOldQuote
 */
class DeleteOldQuote
{

    /**
     * @var \Bss\QuoteExtension\Model\DeleteQuote
     */
    protected $deleteQuote;

    /**
     * @var ManageQuoteFactory
     */
    protected $manageQuoteFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * DeleteOldQuote constructor.
     * @param ManageQuoteFactory $manageQuoteFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Data $helperData
     */
    public function __construct(
        \Bss\QuoteExtension\Model\DeleteQuote $deleteQuote,
        ManageQuoteFactory $manageQuoteFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Data $helperData
    ) {
        $this->deleteQuote = $deleteQuote;
        $this->manageQuoteFactory = $manageQuoteFactory;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->helperData = $helperData;
    }

    /**
     * Cron delete old quote don't use it
     *
     * @return void
     */
    public function execute()
    {
        $enable = $this->helperData->isEnable();
        if ($enable) {
            try {
                $manageQuoteCollection = $this->manageQuoteFactory->create();
                foreach ($manageQuoteCollection as $requestQuote) {
                    $oldQuote = $requestQuote->getOldQuote();
                    if (!$oldQuote) {
                        continue;
                    }
                    $oldQuote = explode(",", $oldQuote);
                    if (is_array($oldQuote)) {
                        foreach ($oldQuote as $quoteId) {
                            $quote = $this->quoteRepository->get($quoteId);
                            $this->quoteRepository->delete($quote);
                        }
                    }
                    $requestQuote->setOldQuote(null)->setNotSendEmail(true)->save();
                }
                $this->deleteQuote->deleteQuote();
            } catch (\Exception $e) {
                $this->logger->debug("Quote Extension Delete Error " . $e->getMessage());
            }
        }
    }
}
