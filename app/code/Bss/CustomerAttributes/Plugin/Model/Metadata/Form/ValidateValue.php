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
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Model\Metadata\Form;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class ValidateValue
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Plugin\Model\Metadata\Form
 */
class ValidateValue
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Customerattribute
     */
    protected $customerAttribute;
    /**
     * @var ConfigFactory
     */
    private $eavAttribute;

    /**
     * ValidateValue constructor.
     * @param Http $request
     * @param Customerattribute $customerAttribute
     */
    public function __construct(
        Http              $request,
        Customerattribute $customerAttribute,
        ConfigFactory     $eavAttributeFactory
    ) {
        $this->request = $request;
        $this->customerAttribute = $customerAttribute;
        $this->eavAttribute = $eavAttributeFactory;
    }

    /**
     * Check validate value
     *
     * @param mixed $subject
     * @param bool $result
     * @return bool
     */
    public function afterValidateValue($subject, $result)
    {
        if ($result === true) { // Returns true immediately to optimize output performance
            return true;
        }

        $page = $this->request->getFullActionName();
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $subject->getAttribute()->getAttributeCode());
        if (method_exists($attribute, 'isUserDefined')) {
            if (!$attribute->isUserDefined()) {
                return $result;
            }
        }
        $usedInForms = $attribute->getUsedInForms();
        if (in_array('is_customer_attribute', $usedInForms)) {
            if (!$this->customerAttribute->getConfig('bss_customer_attribute/general/enable')) {
                return true;
            }
            if (!in_array('customer_account_create_frontend', $usedInForms) && $page == 'customer_account_createpost') {
                return true;
            }

            if (!in_array('customer_account_edit_frontend', $usedInForms) && $page == 'customer_account_editPost') {
                return true;
            }
            if ($attribute->getData('is_required') && $page = 'customerattribute_attribute_save') {
                return true;
            }
        }

        $originalPathInfo = $this->request->getOriginalPathInfo();
        if (($this->request->isAjax() &&
                $originalPathInfo == '/rest/default/V1/carts/mine/payment-information') ||
            $page == 'customer_account_createpost'
        ) {
            return true;
        }

        if ($page == 'sales_order_create_save') {
            $orderSubmit = $this->request->getPost('order');
            $billingAddress = $orderSubmit['billing_address'];
            $valueBilling = '';
            foreach ($billingAddress as $code => $attributeValue) {
                if ($code == $attribute->getAttributeCode()) {
                    $valueBilling = $attributeValue;
                }
            }
            if (array_key_exists('shipping_address', $orderSubmit)) {
                $valueShipping = '';
                $shippingAddress = $orderSubmit['shipping_address'];
                foreach ($shippingAddress as $code => $attributeValue) {
                    if ($code == $attribute->getAttributeCode()) {
                        $valueShipping = $attributeValue;
                    }
                }
                if ($valueShipping !== '' && $valueBilling !== '') {
                    return true;
                }
            }
            if ($valueBilling !== '') {
                return true;
            }

        }
        return $result;
    }
}
