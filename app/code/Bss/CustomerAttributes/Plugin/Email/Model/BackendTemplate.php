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
namespace Bss\CustomerAttributes\Plugin\Email\Model;

/**
 * Class BackendTemplate
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BackendTemplate
{
    /**
     * Add variable customer attributes in template email
     *
     * @param bool $withGroup
     * @return array
     */
    public function afterGetVariablesOptionArray($subject, $result, $withGroup = false)
    {
        $optionArray[] = ['value' => '{{' . 'var bss_customer_attributes' . '|raw}}', 'label' => __('%1', __('Order Customer Attributes'))];
        $optionArray[] = ['value' => '{{' . 'var bss_billing_address_attributes' . '|raw}}', 'label' => __('%1', __('Order Custom Billing Address'))];
        $optionArray[] = ['value' => '{{' . 'var bss_shipping_address_attributes' . '|raw}}', 'label' => __('%1', __('Order Custom Shipping Address'))];
        $optionArray[] = ['value' => '{{' . 'var customer.bss_customer_attributes' . '|raw}}', 'label' => __('%1', __('New Account Customer Attributes'))];

        if ($withGroup) {
            if (!count($result)) {
                $result["label"] = __('Template Variables');
            }
            if (isset($result["value"])) {
                $result["value"] =   array_merge($result["value"], $optionArray);
            } else {
                $result["value"] = $optionArray;
            }

        } else {
            $result = array_merge($result, $optionArray);
        }
        return $result;
    }
}
