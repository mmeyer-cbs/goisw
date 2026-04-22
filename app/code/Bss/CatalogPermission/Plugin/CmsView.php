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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CatalogPermission\Plugin;

/**
 * Class CmsView
 *
 * @package Bss\CatalogPermission\Plugin
 */
class CmsView
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Bss\CatalogPermission\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Bss\CatalogPermission\Helper\CheckPermission
     */
    protected $helperPermission;

    /**
     * CmsView constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig
     * @param \Bss\CatalogPermission\Helper\Data $helper
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Bss\CatalogPermission\Helper\CheckPermission $helperPermission
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig,
        \Bss\CatalogPermission\Helper\Data $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Bss\CatalogPermission\Helper\CheckPermission $helperPermission
    ) {
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->customerSession = $customerSession;
        $this->pageFactory = $pageFactory;
        $this->moduleConfig = $moduleConfig;
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->helperPermission = $helperPermission;
    }

    /**
     * Check Permission CMS page
     *
     * @param \Magento\Cms\Controller\Page\View $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\Result\Redirect|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundExecute(
        \Magento\Cms\Controller\Page\View $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        $pageId = $subject->getRequest()->getParam('page_id', $subject->getRequest()->getParam('id', false));
        $page = $this->getCmsPageById($pageId);
        $data = $page->getData();
        $enableCmsPagePermission = $this->moduleConfig->enableCmsPagePermission();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $arrCustomerGroup = isset($data['bss_customer_group']) && $data['bss_customer_group'] != "" ?
            $this->helper->returnJson()->unserialize($data['bss_customer_group']) : false;
        $redirectPageId = $this->moduleConfig->getPageIdToRedirectCms();
        $customUrl = $this->moduleConfig->getCustomPageUrl();
        $message = $this->moduleConfig->getErrorMessageCms();
        if (isset($data['bss_redirect_type']) && $data['bss_redirect_type'] == 2) {
            $redirectPageId = $data['bss_select_page'];
            $customUrl = $data['bss_custom_url'];
            $message = $data['bss_error_message'];
        }
        if (is_array($arrCustomerGroup) &&
            in_array($customerGroupId, $arrCustomerGroup) &&
            $redirectPageId == $pageId &&
            $enableCmsPagePermission) {
            $this->returnMessage($message);
            return $this->redirectFactory->create()->setPath('404');
        }
        if (is_array($arrCustomerGroup) &&
            in_array($customerGroupId, $arrCustomerGroup) &&
            $redirectPageId != $pageId &&
            $enableCmsPagePermission
        ) {
            if ($this->helperPermission->checkCustomUrl($customUrl)) {
                $this->returnMessage($message);
                return $this->redirectFactory->create()->setPath('no-route');
            }
            $referentUrl = $this->redirect->getRefererUrl();
            $redirectPath = $this->helper->getRedirectUrl($redirectPageId, $customUrl, $referentUrl);
            if ($redirectPath !== false) {
                $this->returnMessage($message);
                return $this->redirectFactory->create()->setPath($redirectPath);
            }
        }
        return $result;
    }

    /**
     * Get cms page by id
     *
     * @param int $id
     * @return \Magento\Cms\Model\Page
     */
    protected function getCmsPageById($id)
    {
        return $this->pageFactory->create()->load($id);
    }

    /**
     * Return error message
     *
     * @param string $message
     */
    private function returnMessage($message)
    {
        if ($message) {
            $this->messageManager->addErrorMessage($message);
        }
    }
}
