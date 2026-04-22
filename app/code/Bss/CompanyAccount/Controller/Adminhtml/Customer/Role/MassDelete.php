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

namespace Bss\CompanyAccount\Controller\Adminhtml\Customer\Role;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Helper\ActionHelper;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Ui\Component\MassAction\Filter;
use Bss\CompanyAccount\Model\ResourceModel\SubRole\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class to delete selected role through massaction
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see MassDelete::_isAllowed()
     */
    const ADMIN_ROLE_DELETE = 'Bss_CompanyAccount::role_delete';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ActionHelper
     */
    private $actionHelper;

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
     * @param ActionHelper $actionHelper
     * @param CollectionFactory $collectionFactory
     * @param SubRoleRepositoryInterface $roleRepository
     * @param JsonFactory $resultJsonFactory
     * @param EmailHelper $emailHelper
     * @param Data $helper
     */
    public function __construct(
        Context                    $context,
        LoggerInterface            $logger,
        Filter                     $filter,
        ActionHelper               $actionHelper,
        CollectionFactory          $collectionFactory,
        SubRoleRepositoryInterface $roleRepository,
        JsonFactory                $resultJsonFactory,
        EmailHelper                $emailHelper,
        Data                       $helper
    ) {
        $this->logger = $logger;
        $this->roleRepository = $roleRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->actionHelper = $actionHelper;
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
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
     * Delete specified roles using grid massaction
     *
     * @return Json
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(): Json
    {
        $error = false;
        $updatedRoleCount = 0;
        $messageErrorEmail = '';
        if ($this->_authorization->isAllowed(self::ADMIN_ROLE_DELETE)) {
            try {
                $customerData = $this->_session->getData('customer_data');
                $collection = $this->filter->getCollection($this->collectionFactory->create());
                $customerId = $customerData['customer_id'];
                $collection->addFieldToFilter(
                    'customer_id',
                    $customerId
                );

                $selected = ($this->getRequest()->getParam('selected'));
                if ((is_array($selected) && in_array(0, $selected)) ||
                    $this->getRequest()->getParam('excluded')
                ) {
                    $isAdminRole = true;
                }
                foreach ($collection->getAllIds() as $roleId) {
                    $roleSendEmail = $this->roleRepository->getRoleToSendMail($roleId);
                    if (!$this->actionHelper->destroyRole($this->roleRepository, $roleId, true)) {
                        $someCant[] = $this->roleRepository->getById($roleId)->getRoleName();
                    } else {
                        $updatedRoleCount++;
                    }
                    if ((int)$roleId !== 0) {
                        if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_UPDATE_ENABLED)
                            && $this->helper->isSendEmailEnable('update')) {
                            $messEma = $this->emailHelper->sendRoleActionToAdmin($customerId, $roleId, 'deleted', $roleSendEmail);
                            if ($messEma) {
                                $messageErrorEmail = $messEma;
                            }
                        }
                    }
                }
                $message = '';
                if ($updatedRoleCount > 0) {
                    $message = __('A total of %1 record(s) have been deleted. ', $updatedRoleCount);
                }
                if (isset($someCant)) {
                    $message .= __('The [%1] role could not delete because they were assigned to sub-user(s). ', implode(', ', $someCant));
                }
                if (isset($isAdminRole)) {
                    $message .= __('You can\'t delete the admin role.');
                }
            } catch (\Exception $e) {
                $message = __('We can\'t mass delete the roles right now.');
                $error = true;
                $this->logger->critical($e);
            }
        } else {
            $error = true;
            $message = __('Sorry, you need permissions to %1.', __('delete selected roles'));
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
