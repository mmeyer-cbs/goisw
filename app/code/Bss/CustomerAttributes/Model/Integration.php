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
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class Integration
 * @package Bss\CustomerAttributes\Model
 */
class Integration implements ArgumentInterface
{
    /**
     * @var Customerattribute
     */
    protected $customerAttribute;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var HandleData
     */
    protected $handleData;

    /**
     * @var SerializeData
     */
    protected $serializeData;

    /**
     * @var ConfigFactory
     */
    protected $eavAttribute;

    /**
     * @var B2BRegistrationIntegrationHelper
     */
    protected $b2BRegistrationIntegration;

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
        $this->customerAttribute = $customerAttribute;
        $this->session = $session;
        $this->handleData = $handleData;
        $this->serializeData = $serializeData;
        $this->eavAttribute = $eavAttribute;
        $this->b2BRegistrationIntegration = $b2BRegistrationIntegration;
    }

    /**
     * @return Customerattribute
     */
    public function getCustomerAttributeHelper()
    {
        return $this->customerAttribute;
    }

    public function getB2BRegistrationIntegrationHelper()
    {
        return $this->b2BRegistrationIntegration;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Display Child Values
     *
     * @return void
     */
    public function displayChildValues()
    {
        return $this->handleData->displayChildValues();
    }

    /**
     * Validate data dependent
     *
     * @param array|mixed $attributeValues
     * @param array|mixed $attributeCollectionData
     * @return array|mixed
     */
    public function validateDependentAttributeInfo($attributeValues, $attributeCollectionData)
    {
        return $this->handleData->validateDependentAttributeInfo($attributeValues, $attributeCollectionData);
    }

    /**
     * Encode function
     *
     * @param mixed|array $data
     * @return bool|string|null
     */
    public function encodeFunction($data)
    {
        return $this->serializeData->encodeFunction($data);
    }

    /**
     * Get Dependent Data
     *
     * @param mixed|string $attrCode
     * @return AttributeDependent
     */
    public function getDependentData($attrCode)
    {
        return $this->handleData->getDependentData($attrCode);
    }

    /**
     * Get List DisableAttribute
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListDisableAttribute($attrData)
    {
        return $this->handleData->getListDisableAttribute($attrData);
    }

    /**
     * Get List AttributeId NotShow
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListAttributeIdNotShow($attrData)
    {
        return $this->handleData->getListAttributeIdNotShow($attrData);
    }

    /**
     * Get List Dependent AttributeCode
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListDependentAttributeCode($attrData)
    {
        return $this->handleData->getListDependentAttributeCode($attrData);
    }

    /**
     * Check module enable Customer Attribute Dependent
     *
     * @return mixed|string
     */
    public function isEnableCustomerAttributeDependency()
    {
        return $this->handleData->isEnableCustomerAttributeDependency();
    }
}
