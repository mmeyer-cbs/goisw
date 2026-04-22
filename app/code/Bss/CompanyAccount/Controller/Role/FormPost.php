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

namespace Bss\CompanyAccount\Controller\Role;

use Bss\CompanyAccount\Exception\EmptyInputException;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\FormHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 *
 * @package Bss\CompanyAccount\Controller\Role
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormPost extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var \Bss\CompanyAccount\Api\SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory
     */
    private $roleFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $url;

    /**
     * @var \Bss\CompanyAccount\Helper\Data
     */
    private $helper;

    /**
     * @var \Bss\CompanyAccount\Helper\ActionHelper
     */
    private $actionHelper;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * FormPost constructor.
     *
     * @param Context $context
     * @param \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory $roleFactory
     * @param LoggerInterface $logger
     * @param FormHelper $formHelper
     * @param \Bss\CompanyAccount\Helper\Data $helper
     * @param \Bss\CompanyAccount\Helper\ActionHelper $actionHelper
     * @param \Magento\Customer\Model\Url $url
     * @param EmailHelper $emailHelper
     */
    public function __construct(
        Context                                              $context,
        \Bss\CompanyAccount\Api\SubRoleRepositoryInterface   $roleRepository,
        \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory $roleFactory,
        LoggerInterface                                      $logger,
        FormHelper                                           $formHelper,
        \Bss\CompanyAccount\Helper\Data                      $helper,
        \Bss\CompanyAccount\Helper\ActionHelper              $actionHelper,
        \Magento\Customer\Model\Url                          $url,
        EmailHelper                                          $emailHelper
    ) {
        $this->helper = $helper;
        $this->actionHelper = $actionHelper;
        $this->roleRepository = $roleRepository;
        $this->roleFactory = $roleFactory;
        $this->logger = $logger;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->url = $url;
        $this->formHelper = $formHelper;
        $this->emailHelper = $emailHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException|\Magento\Framework\Exception\SessionException
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Save role action
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->formHelper->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()
                ->setUrl($this->_redirect->getRefererUrl());
        }
        if (!$this->helper->isCompanyAccount() ||
            !$this->helper->isEnable($this->customerSession->getCustomer()->getWebsiteId())
        ) {
            return $this->resultRedirectFactory->create()
                ->setPath('customer/account/');
        }
        $this->helper->getDataHelper()->getCoreSession()->setRoleFormData(
            $this->getRequest()->getPost()
        );
        $customer = $this->customerSession->getCustomer();
        try {
            $this->helper->getDataHelper()->getCoreSession()->unsRoleFormData();
            $message = $this->actionHelper->saveRole(
                $this->getRequest(),
                $this->roleFactory,
                $this->roleRepository,
                $customer->getEntityId()
            );
            $this->messageManager->addSuccessMessage($message);
            $subRole = $this->roleRepository->getById($this->getRequest()->getParam('role_id'));
            $newRole = $this->getRequest()->getParams();
            if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_UPDATE_ENABLED)
                && $this->helper->isSendEmailEnable('update')
            ) {
                if (!$subRole->getData()) {
                    $messEmail = $this->emailHelper->sendRoleActionToAdmin($customer, $newRole, 'added');
                } else {
                    $messEmail = $this->emailHelper->sendRoleUpdateToAdmin($customer, $subRole);
                }
                if ($messEmail !== '') {
                    $this->messageManager->addErrorMessage(__($messEmail));
                }
            }
            return $this->resultRedirectFactory->create()
                ->setPath('companyaccount/role');
        } catch (EmptyInputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->helper->getDataHelper()->getCoreSession()->setRoleFormData(
                $this->getRequest()->getPost()
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t save role right now.')
            );
            $this->logger->critical($e);
            $this->helper->getDataHelper()->getCoreSession()->setRoleFormData(
                $this->getRequest()->getPost()
            );
        }

        return $this->resultRedirectFactory->create()
            ->setUrl($this->_redirect->getRefererUrl());
    }
}
