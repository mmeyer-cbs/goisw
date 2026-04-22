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

namespace Bss\QuoteExtension\Observer\Quote;

use Bss\QuoteExtension\Model\CreateMaskQuote;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Class MaskQuoteCreate
 *
 * @package Bss\QuoteExtension\Observer\Quote
 */
class MaskQuoteForGuest implements ObserverInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $idMaskFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    protected $quoteToMaskedQuote;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CreateMaskQuote
     */
    private $maskQuoteModel;

    /**
     * MaskQuoteForGuest construct
     *
     * @param QuoteIdMaskFactory $idMaskFactory
     * @param QuoteIdToMaskedQuoteIdInterface $quoteToMaskedQuote
     * @param LoggerInterface $logger
     * @param CreateMaskQuote $maskQuoteModel
     */
    public function __construct(
        QuoteIdMaskFactory              $idMaskFactory,
        QuoteIdToMaskedQuoteIdInterface $quoteToMaskedQuote,
        LoggerInterface                 $logger,
        CreateMaskQuote                 $maskQuoteModel
    ) {
        $this->idMaskFactory = $idMaskFactory;
        $this->quoteToMaskedQuote = $quoteToMaskedQuote;
        $this->logger = $logger;
        $this->maskQuoteModel = $maskQuoteModel;
    }

    /**
     * Create mask quote for guest checkout
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteId = $observer->getData('quoteExtension')->getQuoteId();
        try {
            $checkMaskQuote = $this->quoteToMaskedQuote->execute($quoteId);
            if ($checkMaskQuote == null) {
                $this->maskQuoteModel->createMaskQuote($quoteId);
            }
        } catch (LocalizedException|Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}
