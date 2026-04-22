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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Block\Account;

use Bss\B2bRegistration\Helper\Data;
use Magento\Customer\Model\Url;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class AuthorizationLink extends \Magento\Customer\Block\Account\AuthorizationLink
{
    /**
     * @var $context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Http\Context $httpContext
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Url $customerUrl
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     */
    protected $postDataHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * AuthorizationLink constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Url $customerUrl
     * @param PostHelper $postDataHelper
     * @param Data $helper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        Url $customerUrl,
        PostHelper $postDataHelper,
        Data $helper,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $httpContext, $customerUrl, $postDataHelper, $data);
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        $this->scopeConfig = $context->getScopeConfig();
        $this->moduleManager = $moduleManager;
    }

    /**
     * Enable module
     * @return bool
     */
    public function isEnable()
    {
        return $this->helper->isEnable();
    }

    /**
     * Enable Shortcut Link In Header
     * @return bool
     */
    public function isEnableShortcutLink()
    {
        return $this->helper->isEnableShortcutLink();
    }

    /**
     * Get Shortcut Link Text
     * @return string
     */
    public function getShortcutLinkText()
    {
        return $this->helper->getShortcutLinkText();
    }

    /**
     * Get url in Config module
     * @return string
     */
    public function getB2bUrl()
    {
        return $this->helper->getB2bUrl();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlB2bAccountCreate()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $urlConfig = $this->getB2bUrl();
        $bbCreateUrl = $baseUrl . $urlConfig;
        return  $bbCreateUrl;
    }

    /**
     * Check Force Login Install
     * @return int
     */
    public function checkForceLoginInstall()
    {
        return $this->moduleManager->isOutputEnabled('Bss_ForceLogin');
    }

    /**
     * Check Force Login Enable
     * @return bool
     */
    public function isEnableForceLogin()
    {
        return $this->scopeConfig->isSetFlag(
            'forcelogin/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Enable customer register
     * @return bool
     */
    public function isEnableRegister()
    {
        return $this->scopeConfig->isSetFlag(
            'forcelogin/general/disable_register',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Set Template to Render
     *
     * @param string $template
     * @return AuthorizationLink
     */
    public function setTemplate($template)
    {
        if (!$this->isEnable()) {
            $template = "Magento_Customer::account/link/authorization.phtml";
        }
        return parent::setTemplate($template);
    }
}
