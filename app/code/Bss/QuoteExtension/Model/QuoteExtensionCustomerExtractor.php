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

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory as CustomerFactory;

/**
 * Extract customer data from an order.
 */
class QuoteExtensionCustomerExtractor
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Extract customer data from quote extension.
     *
     * @param int $quoteId
     * @return CustomerInterface
     */
    public function extract(int $quoteId): CustomerInterface
    {
        $customerData = [];
        try {
            $quote = $this->quoteRepository->get($quoteId);

            //Simply return customer from DB.
            if ($quote->getCustomerId()) {
                return $this->customerRepository->getById($quote->getCustomerId());
            }

            $customerData = [
                "firstname" => $quote->getCustomerFirstname(),
                "lastname" => $quote->getCustomerLastname(),
                "email" => $quote->getCustomerEmail()
            ];
        } catch (\Exception $exception) {
        }

        return $this->customerFactory->create(['data' => $customerData]);
    }
}
