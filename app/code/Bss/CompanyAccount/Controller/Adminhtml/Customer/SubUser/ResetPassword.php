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
namespace Bss\CompanyAccount\Controller\Adminhtml\Customer\SubUser;

use Bss\CompanyAccount\Helper\ActionHelper;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Magento\Backend\App\Action;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ResetPassword
 *
 * @package Bss\CompanyAccount\Controller\Adminhtml\customer\SubUser
 */
class ResetPassword extends Action implements HttpPostActionInterface
{
    /**
     * The admin user can execute this action
     *
     * @see _isAllowed()
     */
    const ADMIN_SUB_USER_RESET_PASSWORD = "Bss_CompanyAccount::sub_user_reset_password";

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var SubUserHelper
     */
    private $subUserHelper;

    /**
     * @var ActionHelper
     */
    private $actionHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ResetPassword constructor.
     *
     * @param Action\Context $context
     * @param SubUserHelper $subUserHelper
     * @param ActionHelper $actionHelper
     * @param CustomerRepository $customerRepository
     * @param EmailHelper $emailHelper
     * @param LoggerInterface $logger
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Action\Context $context,
        SubUserHelper $subUserHelper,
        ActionHelper $actionHelper,
        CustomerRepository $customerRepository,
        EmailHelper $emailHelper,
        LoggerInterface $logger,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->customerRepository = $customerRepository;
        $this->emailHelper = $emailHelper;
        $this->subUserHelper = $subUserHelper;
        $this->actionHelper = $actionHelper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Send reset password mail to specific sub-user action
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        if (!$this->_authorization->isAllowed(self::ADMIN_SUB_USER_RESET_PASSWORD)) {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Sorry, you need permissions to %1.', __('reset password sub-user'))
                ]
            );
        }
        $subId = $this->getRequest()->getParam('id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $error = false;
        if (!$customerId || !$subId) {
            $error = true;
        }

        $message = "";
        if (!$error) {
            $customer = $this->customerRepository->getById($customerId);
            try {
                $message = $this->actionHelper->resetPasswordSubUser(
                    $this->subUserHelper,
                    $this->emailHelper,
                    $customer,
                    $subId
                );
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $error = true;
            }
        }

        if ($error) {
            $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Some thing wrong! Please try again.')
                ]
            );
        } else {
            $data = [
                'request_success' => true,
                'messageErrorEmail' => $message,
                'success' => true,
                'error' => false,
                'message' => __('The sub-user will receive an email with a link to reset password.')
            ];
            $resultJson->setData($data);
        }
        return $resultJson;
    }
}
