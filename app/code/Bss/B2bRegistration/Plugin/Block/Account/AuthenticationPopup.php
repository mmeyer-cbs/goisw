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
namespace Bss\B2bRegistration\Plugin\Block\Account;

/**
 * Class AuthenticationPopup
 * @package Bss\B2bRegistration\Plugin\Block\Account
 */
class AuthenticationPopup
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\B2bRegistration\Helper\Data
     */
    protected $helper;

    /**
     * AuthenticationPopup constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\B2bRegistration\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\B2bRegistration\Helper\Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\Block\Account\AuthenticationPopup $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(
        \Magento\Customer\Block\Account\AuthenticationPopup $subject,
        $result
    ) {
        if ($this->helper->isEnable()) {
            $b2bCreateUrl = $this->getUrlB2bAccountCreate();
            $result['enableB2bRegister'] = true;
            $result['b2bRegisterUrl'] = $b2bCreateUrl;
            $result['shortcutLinkText'] = $this->helper->getShortcutLinkText();
            $result['disableB2bRegularRegister'] = $this->helper->disableRegularForm();
        }
        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlB2bAccountCreate()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $urlConfig = $this->helper->getB2bUrl();
        if (!$urlConfig || $urlConfig == '') {
            $urlConfig = 'b2b-customer-create';
        }
        $bbCreateUrl = $baseUrl.$urlConfig;
        return  $bbCreateUrl;
    }
}
