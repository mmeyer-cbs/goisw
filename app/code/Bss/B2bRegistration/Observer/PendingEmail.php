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

use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Helper\Email;

class PendingEmail implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Email
     */
    protected $emailHelper;

    protected $messageManager;

    /**
     * SaveObserver constructor.
     * @param Data $helper
     * @param Email $emailHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Data $helper,
        Email $emailHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
        $this->messageManager = $messageManager;
    }
    /**
     * Send email to customer status:pending
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $email = $this->helper->getCustomerEmailSender();
            $customerEmail = $observer->getData('customer')->getEmail();
            $customerName = $observer->getData('customer')->getFirstname() . " " .
                $observer->getData('customer')->getLastname();
            $emailVar = [
                'varEmail' => $customerEmail,
                'varName' => $customerName,
            ];
            $storeId = $observer->getData('customer')->getStoreId();
            $emailTemplate = $this->helper->getCustomerPendingEmailTemplate($storeId);

            $this->emailHelper->sendEmail($email, $customerEmail, $emailTemplate, $storeId, $emailVar);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage("Some thing happened when sending email");
        }
    }
}
