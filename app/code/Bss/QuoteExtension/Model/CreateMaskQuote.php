<?php

namespace Bss\QuoteExtension\Model;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface;

class CreateMaskQuote
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $idMaskFactory;

    /**
     * @var Random
     */
    private $randomDataGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CreateMaskQuote construct
     *
     * @param QuoteIdMaskFactory $idMaskFactory
     * @param Random $randomDataGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteIdMaskFactory $idMaskFactory,
        Random             $randomDataGenerator,
        LoggerInterface    $logger
    ) {
        $this->idMaskFactory = $idMaskFactory;
        $this->randomDataGenerator = $randomDataGenerator;
        $this->logger = $logger;
    }

    /**
     * Create mask quote
     *
     * @param int|string $quoteId
     * @return void
     */
    public function createMaskQuote($quoteId)
    {
        try {
            $maskQuote = $this->idMaskFactory->create();
            $maskQuote->setQuoteId($quoteId);
            $maskQuote->setMaskedId($this->randomDataGenerator->getUniqueHash());
            $maskQuote->save();
        } catch (LocalizedException|Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}
