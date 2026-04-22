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
namespace Bss\ForceLogin\Plugin\Product;

use Bss\ForceLogin\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Session as CatalogSession;

class View
{
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var CatalogSession
     */
    private $catalogSession;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param Data $helperData
     * @param Session $customerSession
     * @param CatalogSession $catalogSession
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Session $customerSession,
        CatalogSession $catalogSession
    ) {
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->url = $context->getUrl();
        $this->messageManager = $context->getMessageManager();
        $this->catalogSession = $catalogSession;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Force Login for Product Page
     * @param \Magento\Catalog\Controller\Product\View $subject
     * @param \Closure $proceed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(\Magento\Catalog\Controller\Product\View $subject, \Closure $proceed)
    {
        $enableLogin = $this->helperData->isEnable();
        $enableProductPage = $this->helperData->isEnableProductPage();
        if ($enableLogin && $enableProductPage) {
            $customerLogin = $this->customerSession->isLoggedIn();
            if (!$customerLogin) {
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
