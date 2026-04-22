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

declare(strict_types=1);

namespace Bss\CustomerAttributes\Block\Form\Edit;

use Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper;
use Bss\CustomerAttributes\Helper\Customerattribute;
use Bss\CustomerAttributes\Helper\Data;
use Bss\CustomerAttributes\Model\AttributeDependent;
use Bss\CustomerAttributes\Model\AttributeDependentRepository;
use Bss\CustomerAttributes\Model\HandleData;
use Bss\CustomerAttributes\Model\SerializeData;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Customer edit form block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @since 100.0.2
 */
class Edit extends \Magento\Customer\Block\Form\Edit
{
    /**
     * @var AttributeDependentRepository
     */
    protected $attributeDependentRepository;

    /**
     * @var GetAttributeValues
     */
    protected $getAttribute;

    /**
     * @var Customerattribute
     */
    protected $customerAttribute;

    /**
     * @var B2BRegistrationIntegrationHelper
     */
    protected $customerAttributeB2BRegistration;
    /**
     * @var Data $helperData
     */
    protected $helperData;

    /**
     * @var SerializeData
     */
    private $serializer;

    /**
     * @var HandleData
     */
    protected $handleData;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param AttributeDependentRepository $attributeDependentRepository
     * @param GetAttributeValues $getAttribute
     * @param Customerattribute $customerAttribute
     * @param B2BRegistrationIntegrationHelper $customerAttributeB2BRegistration
     * @param Data $helperData
     * @param SerializeData $serializer
     * @param HandleData $handleData
     * @param array $data
     */
    public function __construct(
        Context                          $context,
        Session                          $customerSession,
        SubscriberFactory                $subscriberFactory,
        CustomerRepositoryInterface      $customerRepository,
        AccountManagementInterface       $customerAccountManagement,
        AttributeDependentRepository     $attributeDependentRepository,
        GetAttributeValues               $getAttribute,
        Customerattribute                $customerAttribute,
        B2BRegistrationIntegrationHelper $customerAttributeB2BRegistration,
        Data                             $helperData,
        SerializeData                    $serializer,
        HandleData                       $handleData,
        array                            $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
        $this->attributeDependentRepository = $attributeDependentRepository;
        $this->getAttribute = $getAttribute;
        $this->customerAttribute = $customerAttribute;
        $this->customerAttributeB2BRegistration = $customerAttributeB2BRegistration;
        $this->helperData = $helperData;
        $this->serializer = $serializer;
        $this->handleData = $handleData;
    }

    /**
     * Get Dependent Data
     *
     * @param mixed|string $attrCode
     * @return AttributeDependent
     */
    public function getDependentData($attrCode)
    {
        return $this->attributeDependentRepository->getDataByAttrID($attrCode);
    }

    /**
     * Get Attribute Values
     *
     * @param mixed|string $attribute
     * @return array|null
     */
    public function getAttributeValues($attribute)
    {
        $value = $this->getDependentData($attribute->getData('attribute_id'))->getData();
        return $this->getAttribute->getAttributeValues($value);
    }

    /**
     * Get all function from Bss\CustomerAttributes\Helper\Customerattribute
     *
     * @param $attribute
     * @return Customerattribute
     */
    public function getCustomerAttribute()
    {
        return $this->customerAttribute;
    }

    /**
     * Get all function from Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper
     *
     * @return B2BRegistrationIntegrationHelper
     */
    public function getCustomerAttributeB2BRegistration()
    {
        return $this->customerAttributeB2BRegistration;
    }

    /**
     * Check module enable Customer Attribute Dependent
     *
     * @return mixed|string
     */
    public function isEnableCustomerAttributeDependency()
    {
        return $this->helperData->isEnableCustomerAttributeDependency();
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
     * Validate data dependent
     *
     * @param mixed|array $attributeValues
     * @param mixed|array $attributeCollectionData
     */
    public function validateDependentAttributeInfo($attributeValues, $attributeCollectionData)
    {
        return $this->handleData->validateDependentAttributeInfo($attributeValues, $attributeCollectionData);
    }

    /**
     * Encode function
     *
     * @param mixed|array $data
     * @return bool|string
     */
    public function encodeFunction($data)
    {
        return $this->serializer->encodeFunction($data);
    }
}
