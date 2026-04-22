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

namespace Bss\CompanyAccount\Plugin\Customer;

use Bss\CompanyAccount\Api\SubUserManagementInterface;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ForgotPasswordPost
 *
 * @package Bss\CompanyAccount\Plugin\Customer
 */
class ForgotPasswordPost
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SubUserManagementInterface
     */
    private $subUserManagement;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var SubUserHelper
     */
    private $subUserHelper;

    /**
     * ForgotPasswordPost constructor.
     *
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param EmailHelper $emailHelper
     * @param Escaper $escaper
     * @param RedirectFactory $redirectFactory
     * @param SubUserHelper $subUserHelper
     * @param SubUserManagementInterface $subUserManagement
     */
    public function __construct(
        ManagerInterface           $messageManager,
        StoreManagerInterface      $storeManager,
        EmailHelper                $emailHelper,
        Escaper                    $escaper,
        RedirectFactory            $redirectFactory,
        SubUserHelper              $subUserHelper,
        SubUserManagementInterface $subUserManagement
    ) {
        $this->storeManager = $storeManager;
        $this->subUserManagement = $subUserManagement;
        $this->messageManager = $messageManager;
        $this->emailHelper = $emailHelper;
        $this->redirectFactory = $redirectFactory;
        $this->escaper = $escaper;
        $this->subUserHelper = $subUserHelper;
    }

    /**
     * Before execute post default forgot password
     *
     * Will check if input email is sub-user then send our forgot got password mail
     * else execute default
     *
     * @param \Magento\Customer\Controller\Account\ForgotPasswordPost $subject
     * @param callable $proceed
     *
     * @return callable|\Magento\Framework\Controller\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Customer\Controller\Account\ForgotPasswordPost $subject, callable $proceed)
    {
        try {
            $email = $subject->getRequest()->getPost('email');
            if ($email) {
                $websiteId = $this->storeManager->getWebsite()->getId();
                /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
                $subUser = $this->subUserManagement->getSubUserBy($email, 'sub_email', $websiteId);
                if ($subUser) {
                    $customer = $this->subUserManagement->getCustomerBySubUser($subUser);
                    $subUser = $this->subUserHelper->generateResetPasswordToken($subUser);
                    $this->subUserHelper->save($subUser);
                    $messageEmail = $this->emailHelper->sendResetPasswordMailToSubUser($customer, $subUser);
                    $resultRedirect = $this->redirectFactory->create();

                    if ($messageEmail) {
                        $this->messageManager->addErrorMessage($messageEmail);
                    } else {
                        $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
                    }
                    $resultRedirect->setPath('*/*/');

                    return $resultRedirect;
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $proceed();
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }
}
