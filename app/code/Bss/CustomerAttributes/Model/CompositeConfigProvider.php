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
namespace Bss\CustomerAttributes\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class CompositeConfigProvider
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @package Bss\CustomerAttributes\Model
 */
class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * CompositeConfigProvider constructor.
     *
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Customerattribute $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get required customer attribute
     *
     * @return array
     */
    public function getConfig()
    {
        $output = [];
        $attributeHelper = $this->helper;
        if ($attributeHelper->getConfig('bss_customer_attribute/general/enable')) {
            $config = [];
            $attributeCollection = $attributeHelper->getUserDefinedAttributes();
            foreach ($attributeCollection as $attribute) {
                if ($attribute->getIsRequired() == 1 &&
                    $attributeHelper->isAttribureAddtoCheckout($attribute->getAttributeCode())
                ) {
                    switch ($attribute->getFrontendInput()) {
                        case "multiselect":
                            $config[] = "select[name='bss_customer_attributes["
                                . $attribute->getAttributeCode() . "][]']";
                            break;
                        case "boolean":
                        case "select":
                            $config[] = "select[name='bss_customer_attributes["
                                . $attribute->getAttributeCode() . "]']";
                            break;
                        case "textarea":
                            $config[] = "textarea[name='bss_customer_attributes["
                                . $attribute->getAttributeCode() . "]']";
                            break;
                        default:
                            $config[] = "input[name='bss_customer_attributes[" . $attribute->getAttributeCode() . "]']";
                    }
                }
            }
            $addressCollection = $attributeHelper->getAddressCollection();

            $options = [];
            foreach ($addressCollection as $attribute) {
                $optionAttribute = $this->getOptions($attribute);
                if ($optionAttribute) {
                    $options[$attribute->getAttributeCode()] = $optionAttribute;
                }
            }
            $output["dataCustomAddress"] = $options;
            $output['bssCA']['requireField'] = $config;
        }
        return $output;
    }

    /**
     * Get Options
     *
     * @param \Magento\Customer\Model\Attribute $attribute
     * @return null|array
     */
    protected function getOptions($attribute)
    {
        $options = [];
        $frontendInput = $attribute->getFrontendInput();
        if ($frontendInput == "file") {
            return ["type" => "file"];
        }
        if ($frontendInput == 'text' ||
            $frontendInput == 'textarea' ||
            $frontendInput == 'file' ||
            $frontendInput == 'date'
        ) {
            return null;
        } elseif ($frontendInput == 'boolean') {
            return [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')]
            ];
        } else {
            $optionsList = $this->helper->getAddressAttributeOptions($attribute->getAttributeCode());
            if (is_array($optionsList)) {
                foreach ($optionsList as $option) {
                    if ($option['value'] == '') {
                        continue;
                    }
                    $options[] = ['value' => $option['value'], 'label' => $option['label']];
                }
            }
        }
        return $options;
    }
}
