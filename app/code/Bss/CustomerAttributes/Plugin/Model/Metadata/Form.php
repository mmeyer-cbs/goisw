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
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Plugin\Model\Metadata;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Form
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
     * Eav attribute factory
     * @var Config
     */
    protected $eavAttribute;
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * Form constructor.
     *
     * @param Http $request
     * @param Customerattribute $customerattribute
     * @param ConfigFactory $eavAttributeFactory
     * @param FormFactory $formFactory
     */
    public function __construct(
        Http              $request,
        Customerattribute $customerattribute,
        ConfigFactory     $eavAttributeFactory,
        FormFactory       $formFactory
    ) {
        $this->request = $request;
        $this->customerAttribute = $customerattribute;
        $this->eavAttribute = $eavAttributeFactory;
        $this->formFactory = $formFactory;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return mixed
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllowedAttributes($subject, $result)
    {
        $page = $this->request->getFullActionName();
        $attributeDefault = ['firstname', 'lastname', 'middlename', 'email', 'password', 'taxvat', 'gender', 'dob'];
        if ($page == 'customer_account_editPost'
            && $this->customerAttribute->getConfig('bss_customer_attribute/general/enable')
        ) {
            foreach ($result as $attributeCode => $attribute) {
                if (in_array($attributeCode, $attributeDefault)) {
                    continue;
                }
                $attribute = $this->eavAttribute->create()
                    ->getAttribute('customer', $attributeCode);
                $usedInForms = $attribute->getUsedInForms();
                $currentUsedInForm = "customer_account_edit_frontend";
                if ($this->customerAttribute->checkCustomerB2B()) {
                    $currentUsedInForm = "b2b_account_edit";
                }
                if (!in_array($currentUsedInForm, $usedInForms)) {
                    unset($result[$attributeCode]);
                }
            }
        }
        return $result;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return mixed
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributes($subject, $result)
    {
        $page = $this->request->getFullActionName();

        if ($page == 'sales_order_address' ||
            $page == 'sales_order_create_index' ||
            $page == 'sales_order_create_loadBlock'
        ) {
            foreach ($result as $attribute) {
                if (!$attribute->isUserDefined()) {
                    continue;
                }
                if (!$this->customerAttribute->isCustomAddressAttribute($attribute->getAttributeCode())) {
                    if (strpos($attribute->getAttributeCode(), "ad_") === 0) {
                        unset($result[$attribute->getAttributeCode()]);
                    }
                    continue;
                }
                if ($attribute->getFrontendInput() == 'file' &&
                    strpos($attribute->getAttributeCode(), "ad_") !== 0
                ) {
                    unset($result[$attribute->getAttributeCode()]);
                    continue;
                }
                if ($this->customerAttribute->isCustomAddressAttribute($attribute->getAttributeCode())) {
                    $inputType = $attribute->getFrontendInput();
                    if ($inputType == 'checkboxs') {
                        $attribute->setFrontendInput('multiselect');
                    }
                    if ($inputType == 'radio') {
                        $attribute->setFrontendInput('select');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Compact data array to form attribute values
     *
     * @param $subject
     * @param $result
     * @return array attribute values
     */
    public function afterCompactData($subject, $result)
    {
        /** @var \Magento\Customer\Model\Metadata\Form $addressForm */
        $addressForm = $this->formFactory->create(
            'customer_address',
            'customer_address_edit'
        );

        $customAttribute = $addressForm->getUserAttributes();
        foreach ($customAttribute as $key => $item) {
            if (!$item->isVisible() || !$this->customerAttribute->isEnable()) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * Compact data array to form attribute values
     *
     * @param $subject
     * @param callable $proceed
     * @param $data
     * @return array attribute values
     * @throws LocalizedException
     */
    public function aroundCompactData($subject, callable $proceed, $data)
    {
        $page = $this->request->getFullActionName();
        if ($page == 'customer_address_save'
            && $this->customerAttribute->getConfig('bss_customer_attribute/general/enable')
        ) {
            $fileExist = [];
            foreach ($data as $key => $item) {
                if (!$this->customerAttribute->isCustomAddressAttribute($key)) {
                    continue;
                }
                if (is_array($item)) {
                    if (array_key_exists('file', $item) && !array_key_exists('tmp_name', $item)) {
                        $data[$key] = $item['file'];
                        $fileExist[$key] = $item['file'];
                    }
                }
            }
            $result = $proceed($data);
            if (!empty($fileExist)) {
                foreach ($fileExist as $code => $value) {
                    $result[$code] = $value;
                }
            }
            return $result;
        }
        return $proceed($data);
    }
}
