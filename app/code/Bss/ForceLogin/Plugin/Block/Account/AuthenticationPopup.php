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
namespace Bss\ForceLogin\Plugin\Block\Account;

/**
 * Class AuthenticationPopup
 * @package Bss\ForceLogin\Plugin\Block\Account
 */
class AuthenticationPopup
{
    /**
     * @var \Bss\ForceLogin\Helper\Data
     */
    protected $helperForceLogin;

    /**
     * AuthenticationPopup constructor.
     * @param \Bss\ForceLogin\Helper\Data $helperForceLogin
     */
    public function __construct(
        \Bss\ForceLogin\Helper\Data $helperForceLogin
    ) {
        $this->helperForceLogin = $helperForceLogin;
    }

    /**
     * @param \Magento\Customer\Block\Account\AuthenticationPopup $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(
        \Magento\Customer\Block\Account\AuthenticationPopup $subject,
        $result
    ) {
        if ($this->helperForceLogin->isEnable()) {
            $result['disableRegister'] = $this->helperForceLogin->isEnableRegister();
        }
        return $result;
    }
}
