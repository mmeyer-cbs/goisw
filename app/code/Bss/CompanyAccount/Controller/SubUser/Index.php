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

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Bss\CompanyAccount\Block\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    private $url;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param PageFactory $resultPageFactory
     * @param PermissionsChecker $permissionsChecker
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Url $url,
        PageFactory $resultPageFactory,
        PermissionsChecker $permissionsChecker,
        Data $helper
    ) {
        $this->url = $url;
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->permissionsChecker = $permissionsChecker;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();

        if (!$this->helper->getCustomerSession()->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Sub-user index
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->helper->isCompanyAccount() ||
            !$this->helper->isEnable($this->helper->getCustomerSession()->getCustomer()->getWebsiteId())
        ) {
            return $this->resultRedirectFactory->create()
                ->setUrl($this->_redirect->getRefererUrl());
        }
        try {
            $checkVal = $this->permissionsChecker->check(Permissions::MANAGE_SUB_USER_AND_ROLES);
            if ($checkVal) {
                return $checkVal;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultPage = $this->resultPageFactory->create();
        if ($block = $this->_view->getLayout()->getBlock('bss_companyaccount_subuser_index')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__('Manage Sub-user'));
        return $resultPage;
    }
}
