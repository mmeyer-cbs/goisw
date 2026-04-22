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
namespace Bss\QuoteExtension\Observer\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bss\QuoteExtension\Model\CustomerAssignment;

/**
 * Assign quoteextension to customer created after issuing guest quotextension.
 */
class AssignQuoteExtension implements ObserverInterface
{
    /**
     * @var \Bss\QuoteExtension\Model\ManageQuoteFactory
     */
    protected $quoteExtension;

    /**
     * @var CustomerAssignment
     */
    private $assignmentService;

    /**
     * AssignOrderToCustomerObserver constructor.
     *
     * @param \Bss\QuoteExtension\Model\ManageQuoteFactory $manageQuote
     * @param CustomerAssignment $assignmentService
     */
    public function __construct(
        \Bss\QuoteExtension\Model\ManageQuoteFactory $manageQuote,
        CustomerAssignment $assignmentService
    ) {
        $this->quoteExtension = $manageQuote;
        $this->assignmentService = $assignmentService;
    }

    /**
     * Assignment quotextension to customer
     *
     * @inheritdoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var CustomerInterface $customer */
        $customer = $event->getData('customer_data_object');
        /** @var array $delegateData */
        $delegateData = $event->getData('delegate_data');
        if (array_key_exists('__quoteextension_assign_quote_id', $delegateData)) {
            $quoteId = $delegateData['__quoteextension_assign_quoteextension_id'];
            $quoteExtension = $this->quoteExtension->create()->load($quoteId);
            if (!$quoteExtension->getCustomerId() && $customer->getId()) {
                $this->assignmentService->execute($quoteExtension, $customer);
            }
        }
    }
}
