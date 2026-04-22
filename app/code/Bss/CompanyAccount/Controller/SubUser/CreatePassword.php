<?php
declare(strict_types=1);

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
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CreatePassword
 *
 * @package Bss\CompanyAccount\Controller\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class CreatePassword extends Action
{
    /**
     * @var PageFactory $pageFactory
     */
    protected $pageFactory;

    /**
     * @var SubUserManagementInterface
     */
    private $subUserManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CreatePassword constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param SubUserManagementInterface $subUserManagement
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        SubUserManagementInterface $subUserManagement,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->subUserManagement = $subUserManagement;
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Show create password page
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = $this->getRequest()->getParam('token');
        if (!$resetPasswordToken) {
            $this->messageManager->addErrorMessage('You are trying to reach invalid url.');
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        try {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $this->subUserManagement->validateResetPasswordLinkToken(null, $resetPasswordToken, $websiteId);

            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->pageFactory->create();
            $resultPage->getLayout()
                ->getBlock('resetPassword')
                ->setResetPasswordLinkToken($resetPasswordToken);
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/forgotpassword');

            return $resultRedirect;
        }
    }
}
