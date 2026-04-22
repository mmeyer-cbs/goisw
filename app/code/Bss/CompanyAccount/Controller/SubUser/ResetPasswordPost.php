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
namespace Bss\CompanyAccount\Controller\SubUser;

use Bss\CompanyAccount\Api\SubUserManagementInterface;
use Bss\CompanyAccount\Helper\FormHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ResetPasswordPost
 *
 * @package Bss\CompanyAccount\Controller\SubUser
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResetPasswordPost extends \Magento\Customer\Controller\AbstractAccount implements HttpPostActionInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SubUserManagementInterface
     */
    private $subUserManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param FormHelper $formHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubUserManagementInterface $subUserManagement
     * @param CredentialsValidator|null $credentialsValidator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        FormHelper $formHelper,
        CustomerRepositoryInterface $customerRepository,
        SubUserManagementInterface $subUserManagement,
        LoggerInterface $logger,
        CredentialsValidator $credentialsValidator = null
    ) {
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->subUserManagement = $subUserManagement;
        $this->storeManager = $storeManager;
        $this->formHelper = $formHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     * or change password from sub-user change password tab
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->formHelper->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()
                ->setUrl($this->_redirect->getRefererUrl());
        }
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        if ($resetPasswordToken) {
            /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
            $subUser = $this
                ->subUserManagement
                ->getSubUserBy($resetPasswordToken, 'token', $this->storeManager->getWebsite()->getId());
        } else {
            $subUser = $this->session->getSubUser();
        }
        return $this->validateAndResetPassword($resetPasswordToken, $subUser);
    }

    /**
     * Validate input and reset password
     *
     * @param string|null $resetPasswordToken
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @return \Magento\Framework\Controller\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validateAndResetPassword(
        $resetPasswordToken,
        $subUser
    ) {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $password = (string)$this->getRequest()->getPost('password');
            $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');
            $canUpdate = true;
            if (!$resetPasswordToken) {
                $currentPass = $this->getRequest()->getPost('current_password');
                $isCorrectCurrentPassword = $this->subUserManagement->authenticate($subUser, $currentPass);
                if (!$isCorrectCurrentPassword) {
                    $this->messageManager->addErrorMessage(__('Current Password didn\'t match. Please try again.'));
                    $canUpdate = false;
                }
                $successRedirectPath = 'customer/account';
                $resultRedirect->setPath('*/*/mypassword');
            } else {
                if (!$subUser) {
                    $resultRedirect->setPath('customer/account/forgotpassword/');
                    throw new ExpiredException(__('The password token is incorrect. Reset and try again.'));
                }
                $this->subUserManagement->validateResetPasswordLinkToken(null, $resetPasswordToken, $websiteId);
                $successRedirectPath = 'customer/account/login';
                $resultRedirect->setPath('*/*/createpassword', ['token' => $resetPasswordToken]);
            }
            if (empty($password)) {
                $this->messageManager->addErrorMessage(__('Please enter a new password.'));
                $canUpdate = false;
            }
            if ($password !== $passwordConfirmation) {
                $this->messageManager->addErrorMessage(
                    __('New Password and Confirm New Password values didn\'t match.')
                );
                $canUpdate = false;
            }

            if ($canUpdate) {
                $this->subUserManagement->resetPassword(
                    !$resetPasswordToken ? $subUser->getSubId() : null,
                    $resetPasswordToken,
                    $password,
                    $websiteId
                );
                $this->messageManager->addSuccessMessage(__('You updated your password.'));
                return $resultRedirect->setPath($successRedirectPath);
            }

        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (ExpiredException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the new password.'));
        }

        return $resultRedirect;
    }
}
