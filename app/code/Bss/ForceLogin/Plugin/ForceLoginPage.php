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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Plugin;

use Bss\ForceLogin\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page;
use Magento\Customer\Model\SessionFactory as CustomerSession;

/**
 *  @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForceLoginPage
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var CustomerSession
     */
    protected $sessionFactory;

    /**
     * OtherPage constructor.
     * @param Context $context
     * @param Data $helperData
     * @param Session $authSession
     * @param CustomerSession $sessionFactory
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Session $authSession,
        CustomerSession $sessionFactory
    ) {
        $this->helperData = $helperData;
        $this->url = $context->getUrl();
        $this->messageManager = $context->getMessageManager();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->authSession = $authSession;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * Force login
     *
     * @param Action $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        Action $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        $enableLogin = $this->helperData->isEnable();
        $customerLogin = $this->sessionFactory->create()->getCustomerGroupId();
        $result = $proceed($request);
        $adminSession = $this->authSession->isLoggedIn();
        $resultPage = $result instanceof Page;
        $actionName = $request->getFullActionName();
        $actionName = str_replace("_", "/", $actionName);
        if (!$resultPage && !$customerLogin && !$adminSession) {
            $requestString = $request->getRequestString();
            return $this->checkCatalogSearch($actionName, $requestString, $result);
        }
        if (!$resultPage || !$enableLogin || $customerLogin || $adminSession
            || in_array($actionName, $this->getIgnoreList())
        ) {
            return $result;
        }

        $originalPathInfo = $request->getOriginalPathInfo();
        return $this->returnPage($actionName, $originalPathInfo, $result);
    }

    /**
     * Return page
     *
     * @param string $actionName
     * @param string $path
     * @param string $result
     * @return string|Redirect
     */
    public function returnPage($actionName, $path, $result)
    {
        if ($path && $path != "/") {
            $path = substr_replace($path, "", 0, 1);
        }
        if ($this->checkConfig($actionName, $path)) {
            return $result;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $message = $this->helperData->getAlertMessage();
        if ($message) {
            $this->messageManager->addErrorMessage($message);
        }
        $url = $this->url->getCurrentUrl();
        $this->helperData->setCustomCookie("bss_current_url", $url);
        return $resultRedirect->setPath('customer/account/login');
    }

    /**
     * Check value config forceLogin
     *
     * @param string $actionName
     * @param string $originalPathInfo
     * @return bool
     */
    public function checkConfig($actionName, $originalPathInfo)
    {
        $configForceLoginPage = $this->helperData->getConfigForceLoginPage();
        if ($configForceLoginPage == 0) {
            return true;
        }

        if ($configForceLoginPage == 1) {
            return $this->checkIgnoreListRouter($actionName, $originalPathInfo);
        }
        return !$this->checkForceLoginSpecialPage($actionName, $originalPathInfo);
    }

    /**
     * Check actionName of ignoreRouter all page
     *
     * @param string $actionName
     * @param string $originalPathInfo
     * @return bool
     */
    public function checkIgnoreListRouter($actionName, $originalPathInfo)
    {
        $ignoreList = $this->helperData->getIgnoreListRouter();
        if (!empty($ignoreList)) {
            $ignoreList = str_replace(" ", "", $ignoreList);
            $ignoreList = str_replace("\t", "", $ignoreList);
            $ignoreList = str_replace("\r", "", $ignoreList);
            $ignoreList = str_replace("\n", "", $ignoreList);
            $arrayIgnoreList = explode(",", $ignoreList);
            return $this->checkRouter($arrayIgnoreList, $actionName, $originalPathInfo);
        }
        return false;
    }

    /**
     * Check router is force login special page
     *
     * @param string $actionName
     * @param string $originalPathInfo
     * @return bool
     */
    public function checkForceLoginSpecialPage($actionName, $originalPathInfo)
    {
        $forceRouterSpecialList = $this->helperData->getForceRouterSpecial();

        foreach ($forceRouterSpecialList as $forceRouterSpecial) {
            if ($forceRouterSpecial["url"] && $forceRouterSpecial["type_url"] == "exactly" &&
                $this->checkARouter($forceRouterSpecial["url"], $actionName, $originalPathInfo)) {
                return true;
            }

            if ($forceRouterSpecial["url"] && $forceRouterSpecial["type_url"] == "contain" &&
                (strpos($actionName . "/", $forceRouterSpecial["url"]) === 0 ||
                    strpos($originalPathInfo . "/", $forceRouterSpecial["url"]) === 0)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check list router config is a action or pathInfo
     *
     * @param array $listUrlConfig
     * @param string $action
     * @param string $originalPathInfo
     * @return bool
     */
    public function checkRouter($listUrlConfig, $action, $originalPathInfo)
    {
        foreach ($listUrlConfig as $urlConfig) {
            if ($this->checkARouter($urlConfig, $action, $originalPathInfo)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check a router config is a action or pathInfo
     *
     * @param string $urlConfig
     * @param string $action
     * @param string $originalPathInfo
     * @return bool
     */
    public function checkARouter($urlConfig, $action, $originalPathInfo)
    {
        if ($action == $urlConfig || $action . "/" == $urlConfig ||
            $originalPathInfo == $urlConfig || $originalPathInfo . "/" == $urlConfig
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get IgnoreList
     *
     * @return array
     */
    public function getIgnoreList()
    {
        return [
            'customer/account/login', 'customer/account/loginPost', 'customer/account/logoutSuccess',
            'customer/account/logout', 'customer/account/resetPassword', 'customer/account/resetPasswordpost',
            'customer/account/index', 'customer/account/forgotpassword', 'customer/account/forgotpasswordpost',
            'customer/account/createPassword', 'customer/account/createpassword', 'customer/account/createPost',
            'adminhtml/index/index', 'adminhtml/noroute/index', 'adminhtml/auth/login', 'adminhtml/dashboard/index',
            'adminhtml/auth/logout', 'customer/account/create'
        ];
    }

    /**
     * Check force login with special url catalogsearch
     *
     * @param string $actionName
     * @param string $requestString
     * @param string $result
     * @return string|Redirect
     */
    public function checkCatalogSearch($actionName, $requestString, $result)
    {
        if ($actionName == "catalogsearch/result/index") {
            return $this->returnPage($actionName, $requestString, $result);
        }
        return $result;
    }
}
