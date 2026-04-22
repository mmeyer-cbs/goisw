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

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Bss\CompanyAccount\Helper\FormHelper;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ResetPassword
 *
 * @package Bss\CompanyAccount\Controller\SubUser
 */
class ResetPassword extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var SubUserHelper
     */
    private $subUserHelper;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var \Bss\CompanyAccount\Helper\ActionHelper
     */
    private $actionHelper;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * ResetPassword constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param FormHelper $formHelper
     * @param \Bss\CompanyAccount\Helper\ActionHelper $actionHelper
     * @param SubUserHelper $subUserHelper
     * @param EmailHelper $emailHelper
     * @param \Magento\Customer\Model\Url $url
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Data $helper,
        FormHelper $formHelper,
        \Bss\CompanyAccount\Helper\ActionHelper $actionHelper,
        SubUserHelper $subUserHelper,
        EmailHelper $emailHelper,
        \Magento\Customer\Model\Url $url
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->helper = $helper;
        $this->actionHelper = $actionHelper;
        $this->url = $url;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->subUserHelper = $subUserHelper;
        $this->emailHelper = $emailHelper;
        $this->formHelper = $formHelper;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->formHelper->validate($this->getRequest())) {
            return false;
        }
        if (!$this->helper->isCompanyAccount() ||
            !$this->helper->isEnable($this->customerSession->getCustomer()->getWebsiteId())
        ) {
            $this->messageManager->addErrorMessage(__('You have no access to this action.'));
        }
        try {
            if ($this->getRequest()->isPost()) {
                $subUserId = $this->getRequest()->getParam('sub_id');
                $message = $this->actionHelper->resetPasswordSubUser(
                    $this->subUserHelper,
                    $this->emailHelper,
                    $this->customerSession->getCustomer(),
                    $subUserId
                );
                if ($message) {
                    $this->messageManager->addErrorMessage(__($message));
                }
                $this->messageManager->addSuccessMessage(__('The sub-user will receive an email with a link to reset password.'));
            } else {
                $this->messageManager->addErrorMessage(__('Your request is invalid.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t send reset password to the sub-user right now.'));
            $this->logger->critical($e);
        }
        return false;
    }
}
