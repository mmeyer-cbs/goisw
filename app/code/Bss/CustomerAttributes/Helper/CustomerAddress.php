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

namespace Bss\CustomerAttributes\Helper;

/**
 * Class DataProviderWithDefaultAddresses
 */
class CustomerAddress
{
    const CUSTOMER_ADDRESS = 'customer_address';

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * DataProviderWithDefaultAddresses constructor.
     *
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     */
    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface    $attributeRepository,
        \Bss\CustomerAttributes\Helper\Customerattribute $helper
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->helper = $helper;
    }

    /**
     *  Get data custom address
     *
     * @param array $customerAddress
     * @param array $attributeAddress
     * @return array
     */
    public function getDataCustomAddress($customerAddress, $attributeAddress = null)
    {
        $attributeAddress = $this->helper->converAddressCollectioin();
        $data = [];
        $i = 0;
        foreach ($customerAddress as $attributeCode => $value) {
            if (isset($attributeAddress[$attributeCode])) {
                if ($attributeAddress[$attributeCode]->getFrontendInput() == "file") {
                    $data[$i]["value"] = $this->helper->getFileName($value);
                    $data[$i]["url"] = $this->helper->getViewFile($value, self::CUSTOMER_ADDRESS);
                } elseif ($attributeAddress[$attributeCode]->getFrontendInput() == "date") {
                    $data[$i]["value"] = $this->helper->formatDate($this->helper->getValueAddressAttributeForOrder($attributeAddress[$attributeCode], $value));
                } else {
                    $data[$i]["value"] = $this->helper->getValueAddressAttributeForOrder($attributeAddress[$attributeCode], $value);
                }
                $data[$i]["label"] = $attributeAddress[$attributeCode]->getFrontendLabel();
                $data[$i]['type'] = $attributeAddress[$attributeCode]->getFrontendInput();
                $i++;
            }
        }
        return $data;
    }

    /**
     *  Get data custom address
     *
     * @param array $customerAddress
     * @param array $attributeAddress
     * @return array
     */
    public function getDataCustomAddressGrid($customerAddress)
    {
        $attributeAddress = $this->helper->converAddressCollectioin();
        $data = [];
        $i = 0;
        foreach ($customerAddress as $attributeCode => $attribute) {
            if (isset($attributeAddress[$attributeCode])) {
                if ($attributeAddress[$attributeCode]->getFrontendInput() == "file") {
                    $data[$i]["value"] = $this->helper->getFileName($attribute->getValue());
                    $data[$i]["url"] = $this->helper->getViewFile($attribute->getValue(), self::CUSTOMER_ADDRESS);
                } else {
                    $data[$i]["value"] = $this->helper->getValueAddressAttributeForOrder($attributeAddress[$attributeCode], $attribute->getValue());
                }
                $data[$i]["label"] = $attributeAddress[$attributeCode]->getFrontendLabel();
                $data[$i]['type'] = $attributeAddress[$attributeCode]->getFrontendInput();
                $i++;
            }
        }
        return $data;
    }
}
