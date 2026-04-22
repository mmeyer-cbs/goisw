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
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Ui\Component\MassAction\Filter;
use Bss\CompanyAccount\Model\ResourceModel\SubUser\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class to delete selected sub-user through mass action
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see MassDelete::_isAllowed()
     */
    const ADMIN_SUB_USER_DELETE = 'Bss_CompanyAccount::sub_user_delete';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Filter $filter
     * @param EmailHelper $emailHelper
     * @param CollectionFactory $collectionFactory
     * @param SubUserRepositoryInterface $subUserRepository
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context                    $context,
        LoggerInterface            $logger,
        Filter                     $filter,
        EmailHelper                $emailHelper,
        CollectionFactory          $collectionFactory,
        SubUserRepositoryInterface $subUserRepository,
        JsonFactory                $resultJsonFactory,
        Data                       $helper
    ) {
        $this->logger = $logger;
        $this->subUserRepository = $subUserRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filter = $filter;
        $this->emailHelper = $emailHelper;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Delete specified sub-user using grid mass action
     *
     * @return Json
     */
    public function execute(): Json
    {
        $error = false;
        $deletedCount = 0;
        $messageErrorEmail = '';
        if ($this->_authorization->isAllowed(self::ADMIN_SUB_USER_DELETE)) {
            try {
                $customerData = $this->_session->getData('customer_data');
                $collection = $this->filter->getCollection($this->collectionFactory->create());
                $customerId = $customerData['customer_id'];
                $collection->addFieldToFilter(
                    'customer_id',
                    $customerId
                );
                /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $user */
                foreach ($collection->getItems() as $user) {
                    $this->emailHelper->sendRemoveNotificationMailToSubUser((int)$customerId, $user->getSubId());
                    if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_UPDATE_ENABLED)
                        && $this->helper->isSendEmailEnable('update')) {
                        $messageEmail = $this->emailHelper->sendSubInfoActionToAdmin(
                            (int)$customerId,
                            $user->getSubId(),
                            'deleted'
                        );
                        if ($messageEmail) {
                            $messageErrorEmail = $messageEmail;
                        }
                    }
                    $this->subUserRepository->delete($user);
                    $deletedCount++;
                }
                $message = __('A total of %1 record(s) have been deleted.', $deletedCount);
            } catch (\Exception $e) {
                $message = __('We can\'t mass delete the sub-user right now.');
                $error = true;
                $this->logger->critical($e);
            }
        } else {
            $error = true;
            $message = __('Sorry, you need permissions to %1.', __('delete selected sub-users'));
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
