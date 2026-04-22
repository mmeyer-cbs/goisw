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

use Bss\QuoteExtension\Helper\Data;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as ManageQuoteFactory;
use Bss\QuoteExtension\Model\ResourceModel\QEOldRepository;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class DeleteOldQuote
 */
class DeleteQuote
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Bss\QuoteExtension\Api\Data\QEOldInterface
     */
    protected $qEOldInterface;

    /**
     * @var ResourceModel\QEOldRepository
     */
    protected $qEOldRepository;

    /**
     * DeleteQuote constructor.
     * @param \Bss\QuoteExtension\Api\Data\QEOldInterface $qEOldInterface
     * @param QEOldRepository $qEOldRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Bss\QuoteExtension\Api\Data\QEOldInterface $qEOldInterface,
        \Bss\QuoteExtension\Model\ResourceModel\QEOldRepository $qEOldRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->qEOldInterface = $qEOldInterface;
        $this->qEOldRepository = $qEOldRepository;
    }

    /**
     * Cron delete old quote don't use it
     */
    public function saveQEOld($quoteExtension)
    {
        $oldQuoteIds = $quoteExtension->getQuoteId();
        if ($quoteExtension->getBackendQuoteId()) {
            $oldQuoteIds .= "," . $quoteExtension->getBackendQuoteId();
        }
        if ($quoteExtension->getTargetQuote()) {
            $oldQuoteIds .= "," . $quoteExtension->getTargetQuote();
        }
        if ($quoteExtension->getOldQuote()) {
            $oldQuoteIds .= "," . $quoteExtension->getOldQuote();
        }

        $this->qEOldInterface->setQuoteIds($oldQuoteIds);
        $this->qEOldInterface->setType("Delete request for quote");
        $this->qEOldRepository->save($this->qEOldInterface);
    }

    /**
     * Delete quote old
     */
    public function deleteQuote()
    {
        $listQEOld = $this->qEOldRepository->getAllQEOld();
        foreach ($listQEOld->getItems() as $qEOld) {
            $quoteIds = explode(",", $qEOld->getQuoteIds());
            foreach ($quoteIds as $quoteId) {
                try {
                    $quote = $this->cartRepository->get($quoteId);
                    $this->cartRepository->delete($quote);
                } catch (\Exception $exception) {
                }
            }
            $this->qEOldRepository->delete($qEOld);
        }
    }
}
