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

namespace Bss\CompanyAccount\Controller\Report;

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Bss\CompanyAccount\Controller\Report
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Url
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
     * @param Context $context
     * @param Url $url
     * @param PageFactory $resultPageFactory
     * @param PermissionsChecker $permissionsChecker
     * @param Data $helper
     */
    public function __construct(
        Context            $context,
        Url                $url,
        PageFactory        $resultPageFactory,
        PermissionsChecker $permissionsChecker,
        Data               $helper
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
     * Report index
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
            $checkVal = $this->permissionsChecker->check(Permissions::VIEW_REPORT);
            if ($checkVal) {
                return $checkVal;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultPage = $this->resultPageFactory->create();
        if ($block = $this->_view->getLayout()->getBlock('bss_companyaccount_report_index')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__('Sub-user Report'));
        return $resultPage;
    }
}
