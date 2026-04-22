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

namespace Bss\B2bRegistration\Block\Form\Login;

use Bss\B2bRegistration\Helper\Data;

class Info extends \Magento\Customer\Block\Form\Login\Info
{
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * Info constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param Data $helper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Registration             $registration,
        \Magento\Customer\Model\Url                      $customerUrl,
        \Magento\Checkout\Helper\Data                    $checkoutData,
        \Magento\Framework\Url\Helper\Data               $coreUrl,
        Data                                             $helper,
        \Magento\Framework\Module\Manager                $moduleManager,
        array                                            $data = []
    ) {
        parent::__construct($context, $registration, $customerUrl, $checkoutData, $coreUrl, $data);
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        $this->scopeConfig = $context->getScopeConfig();
        $this->moduleManager = $moduleManager;
    }

    /**
     * Enable module
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->helper->isEnable();
    }

    /**
     * Enable Shortcut Link
     *
     * @return bool
     */
    public function isEnableShortcutLink()
    {
        return $this->helper->isEnableShortcutLink();
    }

    /**
     * Get Shortcut Link Text
     *
     * @return string
     */
    public function getShortcutLinkText()
    {
        return $this->helper->getShortcutLinkText();
    }

    /**
     * Get B2b Account Create Url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlB2bAccountCreate()
    {
        $bbUrl = $this->helper->getB2bUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $bbCreateUrl = $baseUrl . $bbUrl;
        return $bbCreateUrl;
    }

    /**
     * Render block HTML
     *
     * @return string
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        return $this->fetchView($this->getTemplateFile());
    }

    /**
     * Check Disable default registration
     *
     * @return bool
     */
    public function disableRegisterDefault()
    {
        if ($this->isEnable()) {
            $disableRegisterDefaultFromB2b = $this->helper->disableRegularForm();
        } else {
            $disableRegisterDefaultFromB2b = false;
        }
        $disableRegisterDefault = $this->scopeConfig->isSetFlag(
            'forcelogin/general/disable_register',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $enableModuleForceLogin = $this->scopeConfig->isSetFlag(
            "forcelogin/general/enable",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($enableModuleForceLogin) {
            return ($disableRegisterDefault || $disableRegisterDefaultFromB2b) && $enableModuleForceLogin;
        } else {
            return $disableRegisterDefaultFromB2b;
        }
    }
}
