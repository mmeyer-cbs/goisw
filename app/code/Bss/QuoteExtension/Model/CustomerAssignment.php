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

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Assign customer to order.
 */
class CustomerAssignment
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ManageQuote
     */
    protected $quoteExtension;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ManageQuoteFactory $manageQuote
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Bss\QuoteExtension\Model\ManageQuoteFactory  $manageQuote,
        ManagerInterface $eventManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteExtension = $manageQuote;
        $this->eventManager = $eventManager;
    }

    /**
     * Assign customer to quote, quote extension
     *
     * @param ManageQuote $quoteExtension
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($quoteExtension, $customer)
    {
        try {
            $this->assignCustomerToQuote($quoteExtension, $customer);
            $this->assignCustomerToQuoteExtension($quoteExtension, $customer);
        } catch (\Exception $exception) {
        }

        $this->eventManager->dispatch(
            'quoteextension_customer_assign_after',
            [
                'quoteExtension'     => $quoteExtension,
                'customer'  => $customer
            ]
        );
    }

    /**
     * Assign customer to quote extension
     *
     * @param ManageQuote $quoteExtension
     * @param CustomerInterface $customer
     */
    public function assignCustomerToQuoteExtension($quoteExtension, $customer)
    {
        $quoteExtension->setCustomerId($customer->getId())
            ->setCustomerName($customer->getFirstname() . " " . $customer->getLastname())
            ->setCustomerIsGuest(0)
            ->save();
    }

    /**
     * Assign customer to quote extension
     *
     * @param ManageQuote $quoteExtension
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignCustomerToQuote($quoteExtension, $customer)
    {
        $this->assignCustomerToQuoteId($quoteExtension->getQuoteId(), $customer);
    }

    /**
     * Assign customer to quote extension
     *
     * @param ManageQuote $quoteExtension
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignCustomerToQuoteBackend($quoteExtension, $customer)
    {
        if ($backendQuoteId = $quoteExtension->getBackendQuoteId()) {
            $this->assignCustomerToQuoteId($backendQuoteId, $customer);
        }
        if ($targetQuoteId = $quoteExtension->getTargetQuote()) {
            $this->assignCustomerToQuoteId($targetQuoteId, $customer);
        }
    }

    /**
     * Assign customer to quote extension
     *
     * @param int QuoteId
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignCustomerToQuoteId($quoteId, $customer)
    {
        $quote = $this->cartRepository->get($quoteId);
        $quote->setCustomerId($customer->getId());
        $quote->setCustomerGroupId($customer->getGroupId());
        $quote->collectTotals();
        $this->cartRepository->save($quote);
    }
}
