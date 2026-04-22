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
namespace Bss\ForceLogin\Plugin\Cms\Page;

use Bss\ForceLogin\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Cms\Helper\Page;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Session as CatalogSession;

class View
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
     * @var Page
     */
    protected $pagecms;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CatalogSession
     */
    protected $catalogSession;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param Data $helperData
     * @param Session $customerSession
     * @param Page $pagecms
     * @param StoreManagerInterface $storeManager
     * @param CatalogSession $catalogSession
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Session $customerSession,
        Page $pagecms,
        StoreManagerInterface $storeManager,
        CatalogSession $catalogSession
    ) {
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->url = $context->getUrl();
        $this->messageManager = $context->getMessageManager();
        $this->pagecms = $pagecms;
        $this->storeManager = $storeManager;
        $this->catalogSession = $catalogSession;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * AroundExecute
     * @param \Magento\Cms\Controller\Page\View $subject
     * @param \Closure $proceed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return \Magento\Framework\Controller\Result\Redirect|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(\Magento\Cms\Controller\Page\View $subject, \Closure $proceed)
    {
        $enableLogin = $this->helperData->isEnable();
        $enableCmsPage = $this->helperData->isEnableCmsPage();
        $foreceCmsPageId = ',' . $this->helperData->getCmsPageId() . ',';
        $customerLogin = $this->customerSession->isLoggedIn();
        if ($enableLogin && $enableCmsPage && !$customerLogin) {
            $pageId = $subject->getRequest()->getParam('page_id', $subject->getRequest()->getParam('id', false));
            $pageUrl = $this->pagecms->getPageUrl($pageId);
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $cmsUrlkey = ',' . str_replace($baseUrl, '', $pageUrl) . ',';
            $forcecmsPage = strpos($foreceCmsPageId, $cmsUrlkey);
            if ($forcecmsPage !== false) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $currentUrl = $this->url->getCurrentUrl();
                $this->catalogSession->setBssCurrentUrl($currentUrl);
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
