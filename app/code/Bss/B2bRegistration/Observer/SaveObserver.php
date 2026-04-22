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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Helper\Email;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;

class SaveObserver implements ObserverInterface
{
    const NORMAL_ACCOUNT_GROUP = 1;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * SaveObserver constructor.
     * @param Data $helper
     * @param Email $emailHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        Data $helper,
        Email $emailHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Send Email to customer when status is approval or reject
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        if ($customer->getId()) {
            $storeId = $customer->getData('store_id');
            if ($this->helper->isEnable($storeId) && $this->helper->isEnableCustomerEmail($storeId)) {
                $customerId = $customer->getId();
                $currentCustomer = $this->customerRepositoryInterface->getById($customerId);
                $oldStatus = $this->getCustomerStatus($currentCustomer);
                $newStatus = $customer->getData('b2b_activasion_status');
                $customerEmail = $customer->getEmail();
                $customerName = $customer->getName();
                $this->checkAndSend($customer, $newStatus, $oldStatus, $customerEmail, $customerName, $storeId);
            }
        }
    }

    /**
     * @param object $currentCustomer
     * @return int
     */
    protected function getCustomerStatus($currentCustomer)
    {
        $status = $currentCustomer->getCustomAttribute('b2b_activasion_status');
        $status ? $status = $status->getValue() : $status = CustomerAttribute::NORMAL_ACCOUNT;
        return $status;
    }

    /**
     * Send email to Customer when Admin Approval or Reject
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $newStatus
     * @param string $oldStatus
     * @param array $customerEmail
     * @param string $customerName
     * @param int $storeId
     */
    protected function checkAndSend(&$customer, $newStatus, $oldStatus, $customerEmail, $customerName, $storeId)
    {
        $email = $this->helper->getCustomerEmailSender();
        $emailVar = [
            'varEmail'  => $customerEmail,
            'varName' => $customerName,
        ];
        if ($newStatus == CustomerAttribute::B2B_APPROVAL) {
            if ($oldStatus == CustomerAttribute::B2B_PENDING ||
                $oldStatus == CustomerAttribute::B2B_REJECT ||
                $oldStatus == CustomerAttribute::NORMAL_ACCOUNT
            ) {
                $customerGroupId = $this->helper->getCustomerGroup();
                if ($customerGroupId != $customer->getGroupId()) {
                    $customer->setData("b2b_normal_customer_group", $customer->getGroupId());
                }
                $customer->setGroupId($customerGroupId);
                $emailTemplate = $this->helper->getCustomerApproveEmailTemplate($storeId);
                $this->emailHelper->sendEmail($email, $customerEmail, $emailTemplate, $storeId, $emailVar);
            }
        } elseif ($newStatus == CustomerAttribute::B2B_REJECT) {
            if ($oldStatus == CustomerAttribute::B2B_PENDING ||
                $oldStatus == CustomerAttribute::B2B_APPROVAL ||
                $oldStatus == CustomerAttribute::NORMAL_ACCOUNT
            ) {
                $emailTemplate = $this->helper->getCustomerRejectEmailTemplate($storeId);
                $this->emailHelper->sendEmail($email, $customerEmail, $emailTemplate, $storeId, $emailVar);
            }
        } elseif ($newStatus == CustomerAttribute::NORMAL_ACCOUNT &&
            $oldStatus == CustomerAttribute::B2B_PENDING ||
            $oldStatus == CustomerAttribute::B2B_APPROVAL) {
            $normalCustomerGroup = $customer->getData('b2b_normal_customer_group');
            if ($normalCustomerGroup) {
                $customer->setGroupId($normalCustomerGroup);
            } else {
                $customer->setGroupId(self::NORMAL_ACCOUNT_GROUP);
            }
        }
    }
}
