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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Model;

use Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper;
use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Checkout\Model\Session;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class RegisterData
 * @package Bss\CustomerAttributes\Model
 */
class RegisterData extends Integration
{
    /**
     * @var ConfigFactory
     */
    protected $eavAttribute;

    /**
     * RegisterData constructor.
     * @param Customerattribute $customerAttribute
     * @param Session $session
     * @param HandleData $handleData
     * @param SerializeData $serializeData
     * @param ConfigFactory $eavAttribute
     * @param B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
     */
    public function __construct(
        Customerattribute $customerAttribute,
        Session           $session,
        HandleData        $handleData,
        SerializeData     $serializeData,
        ConfigFactory     $eavAttribute,
        B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
    ) {
        $this->eavAttribute = $eavAttribute;
        parent::__construct(
            $customerAttribute,
            $session,
            $handleData,
            $serializeData,
            $eavAttribute,
            $b2BRegistrationIntegration
        );
    }

    /**
     * Check if block is CustomerSectionAttribute
     *
     * @return bool
     */
    public function isCustomerSectionAttribute()
    {
        return true;
    }

    /**
     * Check if attribute available show here
     *
     * @param string|int $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isShowIn($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('personal_infor_section', $usedInForms)
            || in_array('customer_attr_section', $usedInForms)
            || in_array('signin_infor_section', $usedInForms)
        ) {
            return true;
        }
        return false;
    }
}
