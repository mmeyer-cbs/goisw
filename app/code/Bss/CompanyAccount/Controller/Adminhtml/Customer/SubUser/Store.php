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

use Bss\CompanyAccount\Exception\EmailValidateException;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Class Store
 *
 * @package Bss\CompanyAccount\Controller\Adminhtml\customer\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Store extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_SUB_USER_ADD = 'Bss_CompanyAccount::sub_user_add';
    const ADMIN_SUB_USER_EDIT = 'Bss_CompanyAccount::sub_user_edit';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var SubUserHelper
     */
    private $subUserHelper;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Store constructor.
     *
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param SubUserHelper $subUserHelper
     * @param EmailHelper $emailHelper
     * @param Data $helper
     */
    public function __construct(
        Action\Context              $context,
        LoggerInterface             $logger,
        JsonFactory                 $resultJsonFactory,
        SubUserHelper               $subUserHelper,
        EmailHelper                 $emailHelper,
        Data                        $helper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->subUserHelper = $subUserHelper;
        $this->emailHelper = $emailHelper;
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
     * Save sub-user
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id', false);
        $subId = $this->getRequest()->getParam('sub_id', '');
        $error = false;
        $permissionMsg = '';
        if (empty($subId) && !$this->_authorization->isAllowed(self::ADMIN_SUB_USER_ADD)) {
            $error = true;
            $permissionMsg = __('Sorry, you need permissions to %1.', __('create sub-user'));
        } elseif (!empty($subId) && !$this->_authorization->isAllowed(self::ADMIN_SUB_USER_EDIT)) {
            $error = true;
            $permissionMsg = __('Sorry, you need permissions to %1.', __('edit sub-user'));
        }
        try {
            $messageErrorEmail = "";
            $createdSubUser = null;
            $message = !$error ?
                $this->subUserHelper
                    ->createSubUser($this->getRequest(), $customerId, $messageErrorEmail, $createdSubUser) :
                $permissionMsg;

            $this->_eventManager->dispatch(
                'adminhtml_controller_subuser_save_after',
                [
                    'sub_user' => $createdSubUser,
                    'subject' => $this
                ]
            );
            if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_UPDATE_ENABLED)
                && $this->helper->isSendEmailEnable('update')) {
                if ($subUser = $this->subUserHelper->getBy($this->getRequest()->getParam('sub_id'))) {
                    $messageEmail = $this->emailHelper->sendSubInfoUpdateToAdmin((int)$customerId, $subUser);
                } else {
                    $newSubUser = $this->getRequest()->getParams();
                    $messageEmail = $this->emailHelper->sendSubInfoActionToAdmin($customerId, $newSubUser, 'added');
                }
                if ($messageEmail){
                    $messageErrorEmail = $messageEmail;
                }
            }
        } catch (AlreadyExistsException|NotFoundException|EmailValidateException $exception) {
            $error = true;
            $message = $exception->getMessage();
        } catch (\Exception $exception) {
            $error = true;
            $message = __('We can\'t save sub-user right now.');
            $this->logger->critical($exception);
        }

        $subId = empty($subId) ? null : $subId;
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'messageErrorEmail' => $messageErrorEmail,
                'error' => $error,
                'data' => [
                    'sub_id' => $subId
                ]
            ]
        );

        return $resultJson;
    }
}
