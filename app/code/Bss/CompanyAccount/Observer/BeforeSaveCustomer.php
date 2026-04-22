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

namespace Bss\CompanyAccount\Observer;

use Bss\CompanyAccount\Controller\Adminhtml\Customer\SendActiveCompanyAccountEmail;
use Bss\CompanyAccount\Controller\Adminhtml\Customer\SendDeactiveCompanyAccountEmail;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\ResourceModel\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Class BeforeSaveCustomer
 *
 * @package Bss\CompanyAccount\Observer
 */
class BeforeSaveCustomer implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var Customer
     */
    private $customerResource;

    /**
     * BeforeSaveCustomer constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Registry $registry
     * @param Customer $customerResource
     * @param Data $helper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Registry $registry,
        Customer $customerResource,
        Data $helper
    ) {
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->registry = $registry;
        $this->customerResource = $customerResource;
    }

    /**
     * Before save customer observer
     *
     * Before save will check if customer is company account then assign flag to send active
     * email after save.
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Model\Backend\Customer $customer */
        $customer = $observer->getCustomer();

        $subUserResult = $this->customerResource
            ->validateUniqueCustomer($customer->getEmail(), (int) $customer->getWebsiteId());

        $this->registry->unregister('already_exists_email');
        if ($subUserResult) {
            $this->registry->register('already_exists_email', 1);
            throw new AlreadyExistsException(
                __('A user with the same email address already exists in an associated website.')
            );
        }

        /** Begin Check and set flag to send company account status notification after */
        if ($this->helper->isEnable($customer->getWebsiteId())) {
            $this->registry->unregister('bss_send_mail');
            $newCompanyAccountStatus = (int) $customer->getData('bss_is_company_account');
            if ($customer->getId()) {
                $currentCustomer = $this->customerRepository->getById($customer->getId());
                $currentCompanyAccountStatus = $currentCustomer->getCustomAttribute('bss_is_company_account');
            } else {
                $currentCompanyAccountStatus = null;
            }

            if ($currentCompanyAccountStatus !== null) {
                $hasChanged = (int) $currentCompanyAccountStatus->getValue() !== $newCompanyAccountStatus;
                if ($hasChanged) {
                    if ($newCompanyAccountStatus) {
                        $this->registry->register('bss_send_mail', 1);
                    } else {
                        $this->registry->register('bss_send_mail', 0);
                    }
                }
            } else {
                if ($newCompanyAccountStatus) {
                    $this->registry->register('bss_send_mail', 1);
                }
            }
        }
    }
}
