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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\Rule\Condition\Customer;

/**
 * Class AbstractCustomers
 * Abstract Rule customer condition data model
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @package Bss\CustomPricing\Model\Rule\Condition\Customer
 */
abstract class AbstractCustomers extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * Base name for hidden elements.
     *
     * @var string
     */
    protected $elementName = 'general_information[rule]';

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendData;

    /**
     * @var \Bss\CustomPricing\Model\Config\Source\CustomerGroups
     */
    protected $customerGroupsDataSource;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * AbstractCustomers constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Bss\CustomPricing\Model\Config\Source\CustomerGroups $customerGroupsDataSource
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Eav\Model\Config $config,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Backend\Helper\Data $backendData,
        \Bss\CustomPricing\Model\Config\Source\CustomerGroups $customerGroupsDataSource,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = []
    ) {
        $this->customerResource = $customerResource;
        $this->config = $config;
        $this->customerFactory = $customerFactory;
        $this->backendData = $backendData;
        $this->customerGroupsDataSource = $customerGroupsDataSource;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->localeFormat = $localeFormat;
        parent::__construct($context, $data);
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collectValidatedAttributes($customerCollection)
    {
        $attribute = $this->getAttribute();
        if ($attribute != "groups" && $attribute != "specified") {
            $customerCollection->addAttributeToSelect($attribute, 'left');
        }

        return $this;
    }

    /**
     * Retrieve attribute object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\Magento\Eav\Model\Entity\Attribute\AttributeInterface|\Magento\Framework\DataObject
     */
    public function getAttributeObject()
    {
        try {
            $obj = $this->config->getAttribute(\Magento\Customer\Model\Customer::ENTITY, $this->getAttribute());
        } catch (\Exception $e) {
            $obj = new \Magento\Framework\DataObject();
            $obj->setEntity($this->customerFactory->create())->setFrontendInput('text');
        }
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function getValueElementType()
    {
        if ($this->getAttribute() === 'groups') {
            return 'multiselect';
        }
        if ($this->getAttribute() === 'specified') {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }

    /**
     * @inheritDoc
     */
    public function getInputType()
    {
        if ($this->getAttribute() === 'groups') {
            return 'multiselect';
        }
        if ($this->getAttribute() === 'specified') {
            return 'string';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }

    /**
     * @inheritDoc
     */
    public function getValueElementChooserUrl()
    {
        $websiteId = $this->getRule()->getWebsiteId();
        $url = false;
        switch ($this->getAttribute()) {
            case 'specified':
                $url = 'custom_pricing/priceRules_customer/chooser/choose_type/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject() . '/website_id/' . $websiteId;
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->backendData->getUrl($url) : '';
    }

    /**
     * @inheritDoc
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return $this->getData("value_select_options");
    }

    /**
     * @inheritDoc
     */
    public function getValueAfterElementHtml()
    {
        $html = "";
        switch ($this->getAttribute()) {
            case "specified":
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
            default:
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    'Select Specified Customers'
                ) . '" /></a>';
        }
        return $html;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = $this->customerResource->loadAllAttributes()->getAttributesByCode();

        $attributes = [];

        foreach ($customerAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Add special attributes
     *
     * @param array &$attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['groups'] = __('Customer Groups');
        $attributes['specified'] = __('Specified Customers');
    }

    /**
     * Prepares values options to be used as select options or hashed array
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttribute() === 'groups') {
            $selectOptions = $this->customerGroupsDataSource->toOptionArray();
        } else if (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                $selectOptions = $attributeObject->getSource()->getAllOptions();
            }
        }

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    /**
     * Set new values only if we really got them
     *
     * @param array $selectOptions
     * @param array $selectReady
     * @param array $hashedReady
     * @return $this
     */
    protected function _setSelectOptions($selectOptions, $selectReady, $hashedReady)
    {
        if ($selectOptions !== null) {
            // Overwrite only not already existing valuesea
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $option) {
                    if (is_array($option['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$option['value']] = $option['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }
        return $this;
    }

    /**
     * Retrieve value by option
     *
     * @param string|null $option
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();
        return $this->getData('value_option' . ($option !== null ? '/' . $option : ''));
    }

    /**
     * Retrieve attribute element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Load array
     *
     * @param array $arr
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function loadArray($arr)
    {
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();

        $isContainsOperator = !empty($arr['operator']) && in_array($arr['operator'], ['{}', '!{}']);
        if ($attribute && $attribute->getBackendType() == 'decimal' && !$isContainsOperator) {
            if (isset($arr['value'])) {
                if (!empty($arr['operator']) && in_array(
                    $arr['operator'],
                    ['!()', '()']
                ) && false !== strpos(
                    $arr['value'],
                    ','
                )
                ) {
                    $tmp = [];
                    foreach (explode(',', $arr['value']) as $value) {
                        $tmp[] = $this->localeFormat->getNumber($value);
                    }
                    $arr['value'] = implode(',', $tmp);
                } else {
                    $arr['value'] = $this->localeFormat->getNumber($arr['value']);
                }
            } else {
                $arr['value'] = false;
            }
            $arr['is_value_parsed'] = isset(
                $arr['is_value_parsed']
            ) ? $this->localeFormat->getNumber(
                $arr['is_value_parsed']
            ) : false;
        } elseif (!empty($arr['operator']) && $arr['operator'] == '()') {
            if (isset($arr['value'])) {
                $arr['value'] = preg_replace('/\s*,\s*/', ',', $arr['value']);
            }
        }

        return parent::loadArray($arr);
    }

    /**
     * Validate customer attribute value for condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $this->getAttribute();
        if (!$model->getResource()) {
            return false;
        }
        $attr = $model->getResource()->getAttribute($attrCode);

        if ($attr && $attr->getBackendType() == 'datetime' && !is_int($this->getValue())) {
            $this->setValue(strtotime($this->getValue()));
            $value = strtotime($model->getData($attrCode));
            return $this->validateAttribute($value);
        }

        if ($attr && $attr->getFrontendInput() == 'multiselect') {
            $value = $model->getData($attrCode);
            if ($value !== null) {
                $value = strlen($value) ? explode(',', $value) : [];
            } else {
                $value = [];
            }

            return $this->validateAttribute($value);
        }
        return parent::validate($model);
    }

    /**
     * Validate customer by entity ID
     *
     * @param int $customerId
     * @return bool
     */
    public function validateByEntityId($customerId)
    {
        $customer = $this->customerRepositoryInterface->getById($customerId);
        $result = $this->validate($customer);
        unset($customer);
        return $result;
    }
}
