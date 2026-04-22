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

namespace Bss\ForceLogin\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class Data
 * @package Bss\ForceLogin\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_FORCE_LOGIN_PAGE = "forcelogin/page/force_login_page";
    const XML_PATH_ENABLE_MODULE = "forcelogin/general/enable";
    const XML_PATH_ENABLE_PRODUCT_PAGE = "forcelogin/page/product_page";
    const XML_PATH_ENABLE_CATEGORY_PAGE = "forcelogin/page/category_page";
    const XML_PATH_ENABLE_CART_PAGE = "forcelogin/page/cart_page";
    const XML_PATH_ENABLE_CHECKOUT_PAGE = "forcelogin/page/checkout_page";
    const XML_PATH_ENABLE_CONTACT_PAGE = "forcelogin/page/contact_page";
    const XML_PATH_ENABLE_SEARCH_TERM_PAGE = "forcelogin/page/search_term_page";
    const XML_PATH_ENABLE_SEARCH_RESULT_PAGE = "forcelogin/page/search_result_page";
    const XML_PATH_ENABLE_ADVANCED_SEARCH_PAGE = "forcelogin/page/advanced_search_page";
    const XML_PATH_LIST_IGNORE_ROUTER = "forcelogin/page/list_ignore_router";
    const XML_PATH_FORCE_ROUTER_SPECIAL = "forcelogin/page/force_router_special";
    const XML_PATH_DISABLE_REGISTER = "forcelogin/general/disable_register";
    const XML_PATH_ENABLE_CMS_PAGE = "forcelogin/page/enable";

    /**
     * @var Json
     */
    protected $json;
    /**
     * CookieMetadataFactory
     * @var CookieMetadataFactory $cookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * PhpCookieManager
     * @var PhpCookieManager $cookieMetadataManager
     */
    protected $cookieMetadataManager;

    /**
     * @var CatalogSession
     */
    protected $catalogSession;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * Data constructor.
     * @param Json $json
     * @param CatalogSession $catalogSession
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $cookieMetadataManager
     * @param CookieManagerInterface $cookieManager
     * @param Context $context
     */
    public function __construct(
        Json                   $json,
        CatalogSession         $catalogSession,
        CookieMetadataFactory  $cookieMetadataFactory,
        PhpCookieManager       $cookieMetadataManager,
        CookieManagerInterface $cookieManager,
        Context                $context
    ) {
        $this->json = $json;
        $this->catalogSession = $catalogSession;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieMetadataManager = $cookieMetadataManager;
        $this->cookieManager = $cookieManager;
        parent::__construct($context);
    }

    /**
     * Enable module
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config  Force Login Page
     *
     * @return bool
     */
    public function getConfigForceLoginPage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FORCE_LOGIN_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Enable force login for product page
     * @return bool
     */
    public function isEnableProductPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_PRODUCT_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Enable force login for category page
     * @return bool
     */
    public function isEnableCategoryPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_CATEGORY_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Enable force login for cart page
     * @return bool
     */
    public function isEnableCartPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_CART_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Enable force login for checkout page
     * @return bool
     */
    public function isEnableCheckoutPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_CHECKOUT_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Enable force login for contact page
     * @return bool
     */
    public function isEnableContactPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_CONTACT_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Enable force login for search term page
     * @return bool
     */
    public function isEnableSearchTermPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_SEARCH_TERM_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Enable force login for search result page
     *
     * @return bool
     */
    public function isEnableSearchResultPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_SEARCH_RESULT_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Enable force login for advanced search page
     * @return bool
     */
    public function isEnableAdvancedSearchPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_ADVANCED_SEARCH_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Get ignore list router all page
     *
     * @return bool
     */
    public function getIgnoreListRouter()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LIST_IGNORE_ROUTER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get force list router special page
     *
     * @return bool|array
     */
    public function getForceRouterSpecial()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            $configIgnoreListRouterCustomUrl = $this->scopeConfig->getValue(
                self::XML_PATH_FORCE_ROUTER_SPECIAL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($configIgnoreListRouterCustomUrl) {
                return $this->json->unserialize($configIgnoreListRouterCustomUrl);
            }
            return [];
        }
        return false;
    }

    /**
     * Enable customer register
     * @return bool
     */
    public function isEnableRegister()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISABLE_REGISTER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get alert message after redirect login page
     * @return string
     */
    public function getAlertMessage()
    {
        $alertMessage = $this->scopeConfig->getValue(
            'forcelogin/page/message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $alertMessage;
    }

    /**
     * Get redirect url after login
     * @return string
     */
    public function getRedirectUrl()
    {
        $pageRedirect = $this->scopeConfig->getValue(
            'forcelogin/redirect_url/page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $pageRedirect;
    }

    /**
     * Get customer url after login
     * @return string
     */
    public function getCustomUrl()
    {
        $pageRedirect = $this->scopeConfig->getValue(
            'forcelogin/redirect_url/custom_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $pageRedirect;
    }

    /**
     * Enable force login for cms page
     *
     * @return bool
     */
    public function isEnableCmsPage()
    {
        if ($this->getConfigForceLoginPage() == 2) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLE_CMS_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Get cms Page id
     * @return string
     */
    public function getCmsPageId()
    {
        $cmsPageId = $this->scopeConfig->getValue(
            'forcelogin/page/page_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $cmsPageId;
    }

    /**
     * Get Redirect config default
     * @return bool
     */
    public function isRedirectDashBoard()
    {
        $redirectToDashBoard = $this->scopeConfig->isSetFlag(
            'customer/startup/redirect_dashboard',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $redirectToDashBoard;
    }

    /**
     * @return CatalogSession
     */
    public function getSessionCatalog()
    {
        return $this->catalogSession;
    }

    /**
     * Get Cms Index Page Id
     * @param string $pathPage
     * @return mixed
     */
    public function getCmsPageConfig($pathPage)
    {
        return $this->scopeConfig->getValue(
            $pathPage,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve cookie manager
     *
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     * @deprecated
     */
    public function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * @return CookieMetadataFactory|mixed
     */
    public function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Set Custom Cookie
     *
     * @param string $name
     * @param string $value
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setCustomCookie($name, $value)
    {
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);

        $this->cookieManager->setPublicCookie(
            $name,
            $value,
            $publicCookieMetadata
        );
    }

    /**
     * Get Custom Cookie
     *
     * @param string $name
     * @return string|null
     */
    public function getCustomCookie($name)
    {
        return $this->cookieManager->getCookie($name);
    }

    /**
     * Check Enable module B2bRegistration
     *
     * @param mixed $storeId
     * @return mixed
     */
    public function isEnableB2bRegistration($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'b2b/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
