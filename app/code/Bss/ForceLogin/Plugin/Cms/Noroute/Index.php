<?php
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
 * @package    Bss_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Plugin\Cms\Noroute;

use Bss\ForceLogin\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class Index
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param Data $helperData
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Session $customerSession
    ) {
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->url = $context->getUrl();
        $this->messageManager = $context->getMessageManager();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Force Login with no route page
     * @param \Magento\Cms\Controller\Noroute\Index $subject
     * @param \Closure $proceed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(\Magento\Cms\Controller\Noroute\Index $subject, \Closure $proceed)
    {
        $pageId = ',' . $this->helperData->getCmsPageConfig(\Magento\Cms\Helper\Page::XML_PATH_NO_ROUTE_PAGE) . ',';
        $enableLogin = $this->helperData->isEnable();
        $enableCmsPage = $this->helperData->isEnableCmsPage();
        $foreceCmsPageId = ',' . $this->helperData->getCmsPageId() . ',';
        if ($enableLogin && $enableCmsPage) {
            $customerLogin = $this->customerSession->isLoggedIn();
            $forcecmsPage = strpos($foreceCmsPageId, $pageId);
            if (!$customerLogin && $forcecmsPage !== false) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $currentUrl = $this->url->getCurrentUrl();
                $this->helperData->getSessionCatalog()->setBssCurrentUrl($currentUrl);
                $message = $this->helperData->getAlertMessage();
                if ($message) {
                    $this->messageManager->addErrorMessage($message);
                }
                return $resultRedirect->setPath('customer/account/login');
            } else {
                return $proceed();
            }
        } else {
            return $proceed();
        }
    }
}
