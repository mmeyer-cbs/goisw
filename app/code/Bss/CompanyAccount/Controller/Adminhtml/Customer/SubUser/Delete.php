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

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 *
 * @package Bss\CompanyAccount\Controller\Adminhtml\customer\Role
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_SUB_USER_DELETE = 'Bss_CompanyAccount::sub_user_delete';

    /**
     * @var SubUserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Delete constructor.
     *
     * @param SubUserRepositoryInterface $userRepository
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param EmailHelper $emailHelper
     * @param Action\Context $context
     * @param Data $helper
     */
    public function __construct(
        SubUserRepositoryInterface $userRepository,
        LoggerInterface            $logger,
        JsonFactory                $resultJsonFactory,
        EmailHelper                $emailHelper,
        Action\Context             $context,
        Data                       $helper
    ) {
        parent::__construct($context);
        $this->userRepository = $userRepository;
        $this->logger = $logger;
        $this->emailHelper = $emailHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Delete sub-user action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $error = false;
        $messageErrorEmail = '';
        if ($this->_authorization->isAllowed(self::ADMIN_SUB_USER_DELETE)) {
            try {
                $subId = $this->getRequest()->getParam('id');
                $customerId = $this->getRequest()->getParam('customer_id');
                if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_UPDATE_ENABLED)
                    && $this->helper->isSendEmailEnable('update')) {
                    $messEmail = $this->emailHelper->sendSubInfoActionToAdmin(
                        (int)$customerId,
                        (int)$subId,
                        'deleted'
                    );
                    if ($messEmail) {
                        $messageErrorEmail = __('We can\'t send emails right now.');
                    }
                }
                $messageEmail = $this->emailHelper->sendRemoveNotificationMailToSubUser((int)$customerId, (int)$subId);
                if ($messageEmail) {
                    $messageErrorEmail = __('We can\'t send emails right now.');
                }
                $this->userRepository->deleteById((int)$subId);
                $message = __('You deleted the sub-user.');
            } catch (\Exception $e) {
                $error = true;
                $message = __('We can\'t delete the sub-user right now.');
                $this->logger->critical($e);
            }
        } else {
            $error = true;
            $message = __('Sorry, you need permissions to %1.', __('delete sub-user'));
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
                'messageErrorEmail' => $messageErrorEmail
            ]
        );

        return $resultJson;
    }
}
