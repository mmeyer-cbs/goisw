<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Helper;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUserQuote\CollectionFactory as SubQuoteFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;

/**
 * Class GetType
 *
 * @package Bss\CompanyAccount\Helper
 */
class QuoteHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Status active of quote
     *
     * @var bool
     */
    const ACTIVE = 1;

    /**
     * Customer id of bss_sub_quote table
     *
     * @var int
     */
    const ADMIN_SUB_QUOTE_ID = 0;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    private $subQuoteRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SubQuoteFactory
     */
    protected $subQuoteFactory;

    /**
     * GetType constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     * @param SubUserQuoteRepositoryInterface $subQuoteRepository
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param LoggerInterface $logger
     * @param SubQuoteFactory $subQuoteFactory
     */
    public function __construct(
        CartRepositoryInterface         $quoteRepository,
        CollectionFactory               $collectionFactory,
        Context                         $context,
        SubUserQuoteRepositoryInterface $subQuoteRepository,
        Session                         $checkoutSession,
        CustomerSession                 $customerSession,
        LoggerInterface                 $logger,
        SubQuoteFactory                 $subQuoteFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->collectionFactory = $collectionFactory;
        $this->subQuoteRepository = $subQuoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->subQuoteFactory = $subQuoteFactory;
        parent::__construct($context);
    }

    /**
     * Get customer session
     *
     * @return CustomerSession
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Get checkout session
     *
     * @return Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * Get all active quotes per sub-user
     *
     * @param string|int $customerId
     * @return mixed
     */
    private function listActiveQuoteBySubUsers($customerId)
    {
        return $this->collectionFactory->create(
        )->addFieldToFilter('customer_id', $customerId
        )->addFieldToFilter('is_active', self::ACTIVE);
    }

    /**
     * Remove unexpected generated quotes
     *
     * @param int|string $customerId
     * @param int|SubUserInterface $subQuoteId
     * @return void
     */
    public function removeUnsusedQuote($customerId, $subQuoteId)
    {
        if (gettype($subQuoteId) !== 'integer') {
            $subQuoteId = $subQuoteId->getQuoteId();
        }
        $listQuotes = $this->listActiveQuoteBySubUsers($customerId);
        foreach ($listQuotes as $quote) {
            if ($quote->getId() != $subQuoteId) {
                $this->quoteRepository->delete($quote);
            }
        }
    }

    /**
     * Get back quote by user id
     *
     * @param int|string $userId
     * @param string $type
     * @return bool|SubUserQuoteInterface
     */
    public function getBackQuote($userId, $type)
    {
        return $this->subQuoteRepository->getByUserId($userId, $type);
    }

    /**
     * Check approve quote
     *
     * @param int|string $userId
     * @param string $type
     * @return bool|int
     */
    public function checkQuote($userId, $type)
    {
        $backQuote = $this->getBackQuote($userId, $type);
        $currentQuoteId = $this->checkoutSession->getQuoteId();
        if ($backQuote && $currentQuoteId !== $backQuote->getQuoteId()) {
            return $currentQuoteId;
        }
        return false;
    }

    /**
     * Remove unused sub quotes
     *
     * @param int|string $userId
     * @return void
     */
    public function removeSubQuotes($userId)
    {
        $subQuoteCollection = $this->subQuoteFactory->create(
        )->addFieldToFilter('quote_id', ['null' => true]
        )->addFieldToFilter('quote_status', 'active');
        if ((int)$userId > 0) {
            $subQuoteCollection->addFieldToFilter('sub_id', $userId);
        } else {
            $subQuoteCollection->addFieldToFilter('sub_id', self::ADMIN_SUB_QUOTE_ID);
        }
        try {
            foreach ($subQuoteCollection->getItems() as $subQuote) {
                $this->subQuoteRepository->delete($subQuote);
            }
        } catch (CouldNotDeleteException $e) {
            $this->logger->error($e);
        }
    }
}
