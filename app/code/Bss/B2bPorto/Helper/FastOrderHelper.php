<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_B2bPorto
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bPorto\Helper;

/**
 * Class FastOrderHelper
 *
 * @package Bss\B2bPorto\Helper
 */
class FastOrderHelper extends Data
{
    /**
     * @param string $defaultValue
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlShortcut($defaultValue)
    {
        if ($this->getFastOrderConfig('cms_url_key')) {
            return $this->getFastOrderConfig('cms_url_key');
        }
        return $defaultValue;
    }

    /**
     * @param string $config_path
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFastOrderConfig($config_path = '')
    {
        if ($this->scopeConfig->getValue(
            'fastorder/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && $this->checkCustomer()
        ) {
            return $this->scopeConfig->getValue(
                'fastorder/general/' . $config_path,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkCustomer()
    {
        $customerConfig = $this->scopeConfig->getValue(
            'fastorder/general/active_customer_groups',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($customerConfig != '') {
            $customerConfigArr = explode(',', $customerConfig);
            $customerSession = $this->customerSession->create();
            if ($customerSession->isLoggedIn()) {
                $customerId = $customerSession->getId();
                $customerGroupId = $this->customerRepository->getById($customerId)->getGroupId();
                if (in_array($customerGroupId, $customerConfigArr)) {
                    return true;
                }
            } else {
                if (in_array(0, $customerConfigArr)) {
                    return true;
                }
            }
        }
        return false;
    }
}
