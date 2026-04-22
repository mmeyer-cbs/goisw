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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Helper;

use Bss\CustomerAttributes\Model\Config\Source\DisplayBackendCustomerDetail;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AttributeFactory;
use Magento\Customer\Model\Session;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Size;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class B2BRegistrationIntegrationHelper
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @package Bss\CustomerAttributes\Helper
 */
class B2BRegistrationIntegrationHelper extends \Bss\CustomerAttributes\Helper\Customerattribute
{

    /**
     * Check Attr For Edit Page function
     *
     * @param $statusCustomer
     * @param $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function checkAttrForEditPage($statusCustomer, $attributeCode)
    {
        if (!$statusCustomer || $statusCustomer == '0') {
            return $this->isAttributeForNormalAccountEdit($attributeCode);
        } else {
            return $this->isAttributeForB2bAccountEdit($attributeCode);
        }
    }

    /**
     * @param $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isAttributeForNormalAccountEdit($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (!$attribute->getData('is_visible')) {
            return false;
        }
        if ($this->getAttributeDisplay($attributeCode) == DisplayBackendCustomerDetail::B2B_ACCOUNTS) {
            return false;
        }
        if (in_array('customer_account_edit_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * @param $statusCustomer
     * @param $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function checkAddForEditPage($statusCustomer, $attributeCode)
    {
        if (!$statusCustomer || $statusCustomer == '0') {
            return $this->isAddressForNormalAccountEdit($attributeCode);
        }
        return false;
    }

    /**
     * @param $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isAddressForNormalAccountEdit($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer_address', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (in_array('customer_address_edit', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * @param $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isAttributeForB2bAccountEdit($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        if ($this->getAttributeDisplay($attributeCode) == DisplayBackendCustomerDetail::NORMAL_ACCOUNTS) {
            return false;
        }
        if (!$attribute->getData('is_visible')) {
            return false;
        }
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('b2b_account_edit', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in account create
     *
     * @param string $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isAttribureForCustomerAccountCreate($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (in_array('b2b_account_create', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Get type of  Attribute display in
     *
     * @param string $attributeCode
     * @throws LocalizedException
     */
    public function getAttributeDisplay($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        foreach ($usedInForms as $value) {
            return is_numeric($value) ? $value : '';
        }
        return '';
    }
}
