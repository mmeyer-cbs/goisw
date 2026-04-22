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

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\FormHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 *
 * @package Bss\CompanyAccount\Controller\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Delete extends Action
{
    /**
     * @var SubUserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * Delete constructor.
     *
     * @param SubUserRepositoryInterface $userRepository
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param FormHelper $formHelper
     * @param EmailHelper $emailHelper
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        SubUserRepositoryInterface $userRepository,
        LoggerInterface            $logger,
        Data                       $helper,
        FormHelper                 $formHelper,
        EmailHelper                $emailHelper,
        JsonFactory                $jsonFactory,
        Context                    $context
    ) {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
        $this->emailHelper = $emailHelper;
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->jsonFactory = $jsonFactory;
        $this->formHelper = $formHelper;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->formHelper->validate($this->getRequest())) {
            return $this->jsonFactory->create()->setData(['remove_row' => false]);
        }
        $removeRow = true;
        if (!$this->helper->isCompanyAccount() ||
            !$this->helper->isEnable($this->customerSession->getCustomer()->getWebsiteId())
        ) {
            return $this->resultRedirectFactory->create()
                ->setPath('customer/account/');
        }
        try {
            if ($this->getRequest()->isPost()) {
                $subId = $this->getRequest()->getParam('sub_id');
                $removeEmail = $this->emailHelper->sendRemoveNotificationMailToSubUser(null, (int)$subId);
                if ($removeEmail) {
                    $this->messageManager->addErrorMessage(__($removeEmail));
                }
                if ($this->helper->isSendEmailEnable('update')) {
                    if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_UPDATE_ENABLED)) {
                        $customerId = $this->customerSession->getCustomerId();
                        $errorEmail = $this->emailHelper->sendSubInfoActionToAdmin($customerId, (int)$subId, 'deleted');
                        if ($errorEmail !== '') {
                            $this->messageManager->addErrorMessage(__($errorEmail));
                        }
                    }
                }
                $this->userRepository->deleteById((int)$subId);
                $this->messageManager->addSuccessMessage(__('You deleted the sub-user.'));
            }
        } catch (\Exception $e) {
            $removeRow = false;
            $this->messageManager->addErrorMessage(__('We can\'t delete the sub-user right now.'));
            $this->logger->critical($e);
        }

        return $this->jsonFactory->create()
            ->setData(
                ['remove_row' => $removeRow]
            );
    }
}
