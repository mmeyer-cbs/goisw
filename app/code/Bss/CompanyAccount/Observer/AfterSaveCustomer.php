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

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;

/**
 * Class BeforeSaveCustomer
 *
 * @package Bss\CompanyAccount\Observer
 */
class AfterSaveCustomer implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * AfterSaveCustomer constructor.
     *
     * @param EmailHelper $emailHelper
     * @param Registry $registry
     * @param ManagerInterface $messageManager
     * @param Data $helper
     */
    public function __construct(
        EmailHelper $emailHelper,
        Registry $registry,
        ManagerInterface $messageManager,
        Data $helper
    ) {
        $this->emailHelper = $emailHelper;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->helper = $helper;
    }

    /**
     * Before save customer observer
     *
     * Get send mail from before save, check and send mail
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Model\Backend\Customer $customer */
        $customer = $observer->getCustomer();
        $isSendActiveCaEmail = $this->registry->registry('bss_send_mail');
        if ($isSendActiveCaEmail !== null) {
            try {
                if ($isSendActiveCaEmail) {
                    $this->emailHelper->sendActiveCompanyAccountToCustomer($customer);
                } else {
                    $this->emailHelper->sendDeactiveCompanyAccountToCustomer($customer);
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__(
                    'Oops.. Something went wrong when we send mail to customer.')
                );
            }
        }
    }
}
