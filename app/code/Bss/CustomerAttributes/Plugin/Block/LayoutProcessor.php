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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Plugin\Block;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Bss\CustomerAttributes\Helper\Data;
use Bss\CustomerAttributes\Model\AddressAttributeDependentRepository;
use Bss\CustomerAttributes\Model\AttributeDependentRepository;
use Bss\CustomerAttributes\Model\ResourceModel\Option\Collection;
use Bss\CustomerAttributes\Model\SerializeData;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class LayoutProcessor
{
    /**
     * @var Customerattribute
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SerializeData
     */
    protected $serializer;

    /**
     * @var AttributeDependentRepository
     */
    protected $attributeDependentRepository;

    /**
     * @var AddressAttributeDependentRepository
     */
    protected $addressAttributeDependentRepository;
    /**
     * @var Collection
     */
    protected $optionCollection;
    /**
     * @var Data $helperData
     */
    protected $helperData;

    /**
     * @param Customerattribute $helper
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param AttributeDependentRepository $attributeDependentRepository
     * @param AddressAttributeDependentRepository $addressAttributeDependentRepository
     * @param Collection $optionCollection
     * @param Data $helperData
     * @param LoggerInterface $logger
     * @param SerializeData $serializer
     */
    public function __construct(
        Customerattribute                   $helper,
        Session                             $customerSession,
        AccountManagementInterface          $accountManagement,
        \Magento\Checkout\Model\Session     $checkoutSession,
        AttributeDependentRepository        $attributeDependentRepository,
        AddressAttributeDependentRepository $addressAttributeDependentRepository,
        Collection                          $optionCollection,
        Data                                $helperData,
        LoggerInterface                     $logger,
        SerializeData                       $serializer
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->checkoutSession = $checkoutSession;
        $this->attributeDependentRepository = $attributeDependentRepository;
        $this->addressAttributeDependentRepository = $addressAttributeDependentRepository;
        $this->optionCollection = $optionCollection;
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Get Option Value By Id
     *
     * @param string|mixed $optionValue
     * @return mixed
     */
    public function getOptionValueById($optionValue)
    {
        $optionData = $this->optionCollection->getOptionValueById($optionValue);
        foreach ($optionData as $option) {
            return $option->getData()['value'] ?? "N/A";
        }
        return "N/A";
    }

    /**
     * Process Dependent Attribute Options
     *
     * @param array|mixed $options
     * @return array|array[]
     */
    private function processDependAttributeOptions($options)
    {
        return array_map(function ($optionId) {
            return [
                'label' => $this->getOptionValueById($optionId),
                'value' => $optionId
            ];
        }, $options);
    }

    /**
     * Mapping Dependent Attribute Data
     *
     * @param array $data
     * @return array
     */
    protected function mappingDependentAttributeData(array $data): array
    {
        $result = [];
        foreach ($data as $option) {
            if (isset($option['attribute-values']['value'])) {
                $optionId = $option['attribute-values']['value'];
            }

            if (isset($option['attribute-values']['dependent_attribute'])) {
                $dependAttribute = $option['attribute-values']['dependent_attribute'];
                if (isset($dependAttribute['dependent_attribute_value'])) {
                    $dependOptions = &$dependAttribute['dependent_attribute_value'];
                    $dependOptions = $this->processDependAttributeOptions($dependOptions);
                }
            }
            if (isset($optionId) && isset($dependAttribute)) {
                $result[$optionId][] = $dependAttribute;
            }
        }
        return $result;
    }

    /**
     * Get Dependent Data
     *
     * @param mixed|int $attrCode
     * @return \Bss\CustomerAttributes\Api\Data\AttributeInterface|\Bss\CustomerAttributes\Model\AttributeDependent
     */
    public function getDependentData($attrCode)
    {
        return $this->attributeDependentRepository->getDataByAttrID($attrCode);
    }

    /**
     * Get Address Dependent Data
     *
     * @param string|mixed $attrCode
     * @return \Bss\CustomerAttributes\Model\AddressAttributeDependent
     */
    public function getAddressDependentData($attrCode)
    {
        return $this->addressAttributeDependentRepository->getDataByAttrID($attrCode);
    }

    /**
     * After Process
     *
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @codingStandardsIgnoreStart
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array                                            $jsLayout
    ) {
        $hideIfFilledBefore = 1;
        if (!$this->helper->getConfig('bss_customer_attribute/general/enable')) {
            return $jsLayout;
        }
        $customerId = $this->getSessionCustomerId();
        $defaultShippingAddress = false;
        if ($customerId != 0) {
            try {
                $defaultShippingAddress = $this->accountManagement->getDefaultBillingAddress($customerId);
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        $quote = $this->checkoutSession->getQuote();
        $elementTmpl = $this->setElementTmpl();
        $types = $this->setTypes();
        $attributeHelper = $this->helper;
        $attributeCollection = $attributeHelper->getUserDefinedAttributes();
        $addressCollection = $attributeHelper->getAddressCollection();
        $fieldCount = 0;
        $customerAttributeTitleComponent = [
            'component' => 'Bss_CustomerAttributes/js/view/title',
            "template" => "Bss_CustomerAttributes/title",
            'sortOrder' => 600,
        ];
        $addTitle = false;
        $notDisPlayAttribute = [];
        $notAvailableAttribute = [];
        foreach ($attributeCollection as $attribute) {
            $data = $this->serializer->decodeFunction($this->getDependentData($attribute->getAttributeId())->getData('dependents_data'));
            if (!$attribute['is_visible']) {
                array_push($notDisPlayAttribute, $attribute['attribute_code']);
                array_push($notAvailableAttribute, $attribute['attribute_code']);
            }
            if (!empty($data)) {
                foreach ($data as $option) {
                    foreach ($option as $value) {
                        array_push($notDisPlayAttribute, $value['dependent_attribute']['value']);
                    }
                }
            }
        }
        foreach ($attributeCollection as $attribute) {
            if ($customerId != 0) {
                $fieldValue = $attributeHelper->getCustomer($customerId)->getData($attribute->getAttributeCode());
            } else {
                $fieldValue = false;
            }
            if (!$attributeHelper->isAttribureAddtoCheckout($attribute->getAttributeCode())) {
                continue;
            }
            if ($attributeHelper->isHideIfFill($attribute->getAttributeCode()) &&
                ($fieldValue || is_numeric($fieldValue)) &&
                $fieldValue != ''
            ) {
                continue;
            } else {
                $hideIfFilledBefore = 0;
            }
            $label = $attribute->getStoreLabel($attributeHelper->getStoreId());
            $name = $this->setVarName($attribute);
            $validation = $this->setVarValidation($attribute);
            $options = $this->getOptions($attribute);
            $fieldDefaultValue = $attributeHelper->getDefaultValueRequired($attribute);
            $default = $this->setVarDefault($attribute, $fieldValue, $options, $fieldDefaultValue);
            if ($this->helperData->isEnableCustomerAttributeDependency()) {
                $data = $this->serializer->decodeFunction($this->getDependentData($attribute->getAttributeId())->getData('dependents_data'));
                $attributeDependent = $this->mappingDependentAttributeData($data ?? []);
                if (!empty($attributeDependent)) {
                    foreach ($attributeDependent as $attrKey => $attrValue) {
                        foreach ($attrValue as $attrValueKey => $attrValueJr) {
                            if (in_array($attrValueJr['value'], $notAvailableAttribute)) {
                                unset($attributeDependent[$attrKey][$attrValueKey]);
                            }
                        }
                    }
                    foreach ($attributeDependent as $attrKey => $attrValue) {
                        foreach ($attrValue as $attrValueKey => $attrValueJr) {
                            if (isset($attrValue[$attrValueKey + 1])) {
                                if ($attrValue[$attrValueKey]['value'] == $attrValue[$attrValueKey + 1]['value']) {
                                    $attributeDependent[$attrKey][$attrValueKey]['dependent_attribute_value'] = array_merge($attributeDependent[$attrKey][$attrValueKey]['dependent_attribute_value'], $attributeDependent[$attrKey][$attrValueKey + 1]['dependent_attribute_value']);
                                    unset($attributeDependent[$attrKey][$attrValueKey + 1]);
                                }
                            }
                        }
                    }
                }
            } else {
                $attributeDependent = [];
            }
            $componentContent = [
                'component' => $types[$attribute->getFrontendInput()],
                'config' => [
                    'customScope' => "shippingAddressLogin",
                    'template' => 'ui/form/field',
                    'elementTmpl' => $elementTmpl[$attribute->getFrontendInput()],
                    'id' => $attribute->getAttributeCode(),
                ],
                'options' => $options,
                'dataScope' => "shippingAddressLogin." . $name,
                'label' => $label,
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => $validation,
                'sortOrder' => $attribute->getSortOrder() + 500,
                'id' => 'bss_customer_attribute[' . $attribute->getAttributeCode() . ']',
                'default' => $default,
                'attributeDependent' => $attributeDependent
            ];

            if (in_array($attribute->getAttributeCode(), $notDisPlayAttribute)
                && $this->helperData->isEnableCustomerAttributeDependency()) {
                $componentContent['visible'] = false;
            }
            if (!$this->helperData->isEnableCustomerAttributeDependency() && !$attribute['is_visible']) {
                $componentContent['visible'] = false;
            }
            if ($attribute->getFrontendInput() !== 'boolean') {
                $componentContent['caption'] = __('Please select');
            }
            if ($attribute->getFrontendInput() == 'file') {
                $urlUploadFile = $this->helper->getUrlUploadFile();
                $componentContent["config"]['uploaderConfig']['url'] = $urlUploadFile;
            }
            if ($quote->getIsVirtual() == 1) {
                if (!$addTitle) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['afterMethods']['children']['customer-attribute-title'] = $customerAttributeTitleComponent;
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['afterMethods']['children'][$attribute->getAttributeCode()] = $componentContent;
            } elseif ($defaultShippingAddress) {
                if (!$addTitle) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['before-form']['children']['customer-attribute-title'] = $customerAttributeTitleComponent;
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['before-form']['children'][$attribute->getAttributeCode()] = $componentContent;
            } else {
                if (!$addTitle) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['shipping-address-fieldset']['children']['customer-attribute-title'] = $customerAttributeTitleComponent;
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'][$attribute->getAttributeCode()] = $componentContent;
            }
            $addTitle = true;
            $fieldCount++;
            if ($attribute->getFrontendInput() == 'file') {
                $jsLayout = $this->processCustomAttributesForPaymentMethods($jsLayout, $attribute->getAttributeCode(), $componentContent);
            }
        }
        if ($hideIfFilledBefore == 0) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['beforeMethods']['children']['bss-customer-attributes-validate'] = [
                'component' => 'Bss_CustomerAttributes/js/view/payment-validation',
                'sortOrder' => 900
            ];
        }
        $customer = $attributeHelper->getCustomer($customerId);
        $newAddress = 0;
        if (($customerId !== 0 && empty($customer->getAddresses())) || $customerId == 0) {
            $newAddress = 1;
        }
        $notDisPlayAddAttribute = [];
        foreach ($addressCollection as $attribute) {
            $addressData = $this->serializer->decodeFunction($this->getAddressDependentData($attribute->getAttributeId())->getData('dependents_data'));
            if (!$attribute['is_visible']) {
                array_push($notDisPlayAttribute, $attribute['attribute_code']);
            }
            if (!empty($addressData)) {
                foreach ($addressData as $option) {
                    foreach ($option as $value) {
                        array_push($notDisPlayAddAttribute, $value['dependent_attribute']['value']);
                    }
                }
            }
        }
        foreach ($addressCollection as $attribute) {
            if ($customerId != 0) {
                $fieldValue = $attributeHelper->getCustomer($customerId)->getData($attribute->getAttributeCode());
            } else {
                $fieldValue = false;
            }
            if (!$attribute->getIsVisible() || !$attributeHelper->isAddressAddToCheckout($attribute->getAttributeCode())) {
                continue;
            }

            $label = $attribute->getStoreLabel($attributeHelper->getStoreId());
            $name = $this->setAddressVarName($attribute);
            $validation = $this->setVarValidation($attribute);
            $options = $this->getAddressOption($attribute);
            $fieldDefaultValue = $attributeHelper->getDefaultValueRequired($attribute);
            $default = $this->setVarDefault($attribute, $fieldValue, $options, $fieldDefaultValue);
            if ($this->helperData->isEnableCustomerAttributeDependency()) {
                $addressData = $this->serializer->decodeFunction($this->getAddressDependentData($attribute->getAttributeId())->getData('dependents_data'));
                $attributeDependent = $this->mappingDependentAttributeData($addressData ?? []);
            } else {
                $attributeDependent = [];
            }
            $componentContent = [
                'component' => $types[$attribute->getFrontendInput()],
                'config' => [
                    'customScope' => "shippingAddressLogin",
                    'template' => 'ui/form/field',
                    'elementTmpl' => $elementTmpl[$attribute->getFrontendInput()],
                    'id' => $attribute->getAttributeCode()
                ],
                'options' => $options,
                'dataScope' => "shippingAddressLogin." . $name,
                'label' => $label,
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => $validation,
                'sortOrder' => $attribute->getSortOrder() + 500,
                'id' => 'bss_customer_address[' . $attribute->getAttributeCode() . ']',
                'default' => $default,
                'attributeDependent' => $attributeDependent
            ];

            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['payments-list']['children'])) {
                $paymentMethodRenders = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['payments-list']['children'];
                if (is_array($paymentMethodRenders)) {
                    foreach ($paymentMethodRenders as $name => $renderer) {
                        if (isset($renderer['children']) && array_key_exists('form-fields', $renderer['children'])) {
                            $paymentCode = str_replace('-form', '', $name);
                            $nameBilling = $this->setAddressVarNameBilling($attribute, $paymentCode);
                            $componentContentBilling = [
                                'component' => $types[$attribute->getFrontendInput()],
                                'config' => [
                                    'customScope' => "shippingAddressLogin",
                                    'template' => 'ui/form/field',
                                    'elementTmpl' => $elementTmpl[$attribute->getFrontendInput()],
                                    'id' => $attribute->getAttributeCode()
                                ],
                                'options' => $options,
                                'dataScope' => "shippingAddressLogin." . $nameBilling,
                                'label' => $label,
                                'provider' => 'checkoutProvider',
                                'visible' => true,
                                'validation' => $validation,
                                'sortOrder' => $attribute->getSortOrder() + 500,
                                'id' => 'bss_customer_address[' . $attribute->getAttributeCode() . ']',
                                'default' => $default,
                                'attributeDependent' => $attributeDependent
                            ];
                            if (in_array($attribute->getAttributeCode(), $notDisPlayAddAttribute) &&
                                $this->helperData->isEnableCustomerAttributeDependency()) {
                                $componentContentBilling['visible'] = false;
                            }
                            if (!$this->helperData->isEnableCustomerAttributeDependency() && !$attribute['is_visible']) {
                                $componentContentBilling['visible'] = false;
                            }
                            if ($attribute->getFrontendInput() == 'file') {
                                $componentContentBilling["config"]["uploaderConfig"]["url"] = $this->helper->getUrlUploadFileAdress();
                            }
                            if ($attribute->getFrontendInput() !== 'boolean') {
                                $componentContentBilling['caption'] = __('Please select');
                            }
                            $this->addNewBillingAddress($jsLayout, $componentContentBilling, $attribute, $paymentCode, $name);
                        }
                    }
                }
            }
            if (in_array($attribute->getAttributeCode(), $notDisPlayAddAttribute)
                && $this->helperData->isEnableCustomerAttributeDependency()) {
                $componentContent['visible'] = false;
            }
            if (!$this->helperData->isEnableCustomerAttributeDependency() && !$attribute['is_visible']) {
                $componentContent['visible'] = false;
            }
            if ($attribute->getFrontendInput() == 'file') {
                $componentContent["config"]["uploaderConfig"]["url"] = $this->helper->getUrlUploadFileAdress();
            }
            if ($attribute->getFrontendInput() !== 'boolean') {
                $componentContent['caption'] = __('Please select');
            }

            if ($newAddress) {
                if ($quote->getIsVirtual() == 1) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['afterMethods']['children'][$attribute->getAttributeCode()] = $componentContent;
                } elseif ($defaultShippingAddress) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['before-form']['children'][$attribute->getAttributeCode()] = $componentContent;
                } else {
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['shipping-address-fieldset']['children'][$attribute->getAttributeCode()] = $componentContent;
                }
                $fieldCount++;
            }

            $this->addNewShippingAddress($jsLayout, $componentContent, $attribute);
        }

        if ($fieldCount > 0) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['bss-customer-attributes-validate'] = [
                'component' => 'Bss_CustomerAttributes/js/view/customer-attributes-validate',
                'sortOrder' => 900
            ];

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['beforeMethods']['children']['bss-customer-attributes-validate'] = [
                'component' => 'Bss_CustomerAttributes/js/view/payment-validation',
                'sortOrder' => 900
            ];
        }

        return $jsLayout;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Set var var validation
     *
     * @param Attribute $attribute
     * @return array
     */
    private function setVarValidation($attribute)
    {
        $validation = [];
        if ($attribute->getIsRequired() == 1) {
            if ($attribute->getFrontendInput() == 'multiselect') {
                $validation['validate-one-required'] = true;
                $validation['required-entry'] = true;
            } else {
                $validation['required-entry'] = true;
            }
        }
        if ($attribute->getFrontendClass()) {
            $validation[$attribute->getFrontendClass()] = true;
        }

        if ($attribute->getFrontendInput() == 'date') {
            $validation['validate-date'] = 'm/d/Y';
        }
        return $validation;
    }

    /**
     * Set var default
     *
     * @param Attribute $attribute
     * @param string $fieldValue
     * @param array $options
     * @param mixed $fieldDefaultValue
     * @return array
     */
    private function setVarDefault($attribute, $fieldValue, $options, $fieldDefaultValue)
    {
        $default = [];
        $selectedOptions = [];
        $selectList = ['select', 'boolean', 'multiselect', 'checkboxs'];
        if (!is_array($fieldValue) && $fieldValue) {
            $selectedOptions = explode(',', $fieldValue);
        }
        if (in_array($attribute->getFrontendInput(), $selectList)) {
            if ($fieldValue || $fieldValue === "0") {
                $optionReBuild = [];
                foreach ($options as $option) {
                    $optionReBuild[] = $option['value'];
                }
                $default = array_intersect($selectedOptions, $optionReBuild);
            } elseif ($fieldDefaultValue) {
                $default = explode(',', $fieldDefaultValue);
            }
        } else {
            if ($attribute->getFrontendInput() == 'date') {
                if ($fieldValue) {
                    $default = $this->helper->formatDate($fieldValue);
                } else {
                    $default = $attribute->getDefaultValue();
                }
            } else {
                if ($fieldValue) {
                    $default = $fieldValue;
                } else {
                    $default = $attribute->getDefaultValue();
                }
            }
        }
        return $default;
    }

    /**
     * Get Options
     *
     * @param Attribute $attribute
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getOptions($attribute)
    {
        $options = [];
        if ($attribute->getFrontendInput() == 'text' ||
            $attribute->getFrontendInput() == 'textarea' ||
            $attribute->getFrontendInput() == 'file'
        ) {
            return $options;
        }
        if ($attribute->getFrontendInput() == 'date') {
            $options = [
                "dateFormat" => 'M/d/Y',
            ];
        } elseif ($attribute->getFrontendInput() == 'boolean') {
            $options = [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')]
            ];
        } else {
            $optionsList = $this->helper->getAttributeOptions($attribute->getAttributeCode());
            foreach ($optionsList as $option) {
                if ($option['value'] == '') {
                    continue;
                }
                $options[] = ['value' => $option['value'], 'label' => $option['label']];
            }
        }
        return $options;
    }

    /**
     * Get Options
     *
     * @param Attribute $attribute
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAddressOption($attribute)
    {
        $options = [];
        if ($attribute->getFrontendInput() == 'text' ||
            $attribute->getFrontendInput() == 'textarea' ||
            $attribute->getFrontendInput() == 'file'
        ) {
            return $options;
        }
        if ($attribute->getFrontendInput() == 'date') {
            $options = [
                "dateFormat" => 'M/d/Y',
            ];
        } elseif ($attribute->getFrontendInput() == 'boolean') {
            $options = [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')]
            ];
        } else {
            $optionsList = $this->helper->getAddressAttributeOptions($attribute->getAttributeCode());
            foreach ($optionsList as $option) {
                if ($option['value'] == '') {
                    continue;
                }
                $options[] = ['value' => $option['value'], 'label' => $option['label']];
            }
        }
        return $options;
    }

    /**
     * Set Variable Name
     *
     * @param Attribute $attribute
     * @return string
     */
    private function setVarName($attribute)
    {
        if ($attribute->getFrontendInput() == 'multiselect') {
            $name = 'bss_customer_attributes[' . $attribute->getAttributeCode() . '][]';
        } else {
            $name = 'bss_customer_attributes[' . $attribute->getAttributeCode() . ']';
        }
        return $name;
    }

    /**
     * Set Variable Name For Address Field
     *
     * @param Attribute $attribute
     * @return string
     */
    private function setAddressVarName($attribute)
    {
        if ($attribute->getFrontendInput() == 'multiselect') {
            $name = 'shippingAddress.custom_attributes.[' . $attribute->getAttributeCode() . '][]';
        } else {
            $name = 'shippingAddress.custom_attributes.' . $attribute->getAttributeCode();
        }
        return $name;
    }

    /**
     * Set Variable Name For Address Field
     *
     * @param Attribute $attribute
     * @param string $paymentCode
     * @return string
     */
    private function setAddressVarNameBilling($attribute, $paymentCode)
    {
        if ($attribute->getFrontendInput() == 'multiselect') {
            $name = 'billingAddress' . $paymentCode . '.custom_attributes.[' . $attribute->getAttributeCode() . '][]';
        } else {
            $name = 'billingAddress' . $paymentCode . '.custom_attributes.' . $attribute->getAttributeCode();
        }
        return $name;
    }

    /**
     * Set Types
     *
     * @return array
     */
    private function setTypes()
    {
        return [
            'text' => 'Bss_CustomerAttributes/js/form/element/text',
            'textarea' => 'Bss_CustomerAttributes/js/form/element/textarea',
            'date' => 'Bss_CustomerAttributes/js/form/element/date',
            'boolean' => 'Bss_CustomerAttributes/js/form/element/select',
            'select' => 'Bss_CustomerAttributes/js/form/element/select',
            'radio' => 'Bss_CustomerAttributes/js/form/element/select',
            'multiselect' => 'Bss_CustomerAttributes/js/form/element/multiselect',
            'checkboxs' => 'Bss_CustomerAttributes/js/form/element/checkboxes',
            'file' => 'Bss_CustomerAttributes/js/form/element/file'
        ];
    }

    /**
     * Set Element Tmpl
     *
     * @return array
     */
    private function setElementTmpl()
    {
        return [
            'text' => 'ui/form/element/input',
            'textarea' => 'ui/form/element/textarea',
            'date' => 'ui/form/element/date',
            'select' => 'ui/form/element/select',
            'boolean' => 'ui/form/element/select',
            'radio' => 'Bss_CustomerAttributes/form/element/radio',
            'multiselect' => 'ui/form/element/multiselect',
            'checkboxs' => 'Bss_CustomerAttributes/form/element/checkboxes',
            'file' => 'ui/form/element/uploader/uploader'
        ];
    }

    /**
     * Get Customer Id
     *
     * @return int|null
     */
    private function getSessionCustomerId()
    {
        if ($this->customerSession->getCustomerId()) {
            return $this->customerSession->getCustomerId();
        }
        return 0;
    }

    /**
     * Render shipping address for payment methods.
     *
     * @param array $jsLayout
     * @param array $componentContent
     * @param string $attributeCode
     * @return array
     */
    private function processCustomAttributesForPaymentMethods(
        array $jsLayout,
        $attributeCode,
        $componentContent
    ) {
        // The following code is a workaround for custom address attributes
        $paymentMethodRenders = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children'];
        if (is_array($paymentMethodRenders)) {
            foreach ($paymentMethodRenders as $name => $renderer) {
                if (isset($renderer['children']) && array_key_exists('form-fields', $renderer['children'])) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['payments-list']['children'][$name]['children']
                    ['form-fields']['children'][$attributeCode] = $componentContent;
                }
            }
        }

        return $jsLayout;
    }

    /**
     * Add new shipping address
     *
     * @param array $jsLayout
     * @param array $componentContent
     * @param Attribute $attribute
     */
    public function addNewShippingAddress(&$jsLayout, $componentContent, $attribute)
    {
        if ($attribute->getFrontendInput() == 'multiselect') {
            $componentContent["dataScope"] = 'shippingAddress.custom_attributes.' . $attribute->getAttributeCode();
        }
        $componentContent["config"]["customScope"] = "shippingAddress.custom_attributes";
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
        [$attribute->getAttributeCode()] = $componentContent;
    }

    /**
     * Add new billing address
     *
     * @param array $jsLayout
     * @param array $componentContent
     * @param Attribute $attribute
     * @param string $paymentCode
     * @param string $name
     */
    public function addNewBillingAddress(&$jsLayout, $componentContent, $attribute, $paymentCode, $name)
    {
        if ($attribute->getFrontendInput() == 'multiselect') {
            $componentContent["dataScope"]
                = 'billingAddress' . $paymentCode . '.custom_attributes.' . $attribute->getAttributeCode();
        }
        $componentContent["config"]["customScope"] = 'billingAddress' . $paymentCode . '.custom_attributes';
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children'][$name]['children']
        ['form-fields']['children'][$attribute->getAttributeCode()] = $componentContent;
    }

    /**
     * Decode function
     *
     * @param mixed|array $data
     * @return array|bool|float|int|string|null
     */
    public function decodeFunction($data)
    {
        return $this->serializer->decodeFunction($data);
    }
}
