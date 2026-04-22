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
namespace Bss\CustomerAttributes\ViewModel\Address;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Helper\Session\CurrentCustomerAddress;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CustomAddressHelper implements ArgumentInterface
{
    /**
     * @var Customerattribute
     */
    private $attributeHelper;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var CurrentCustomerAddress
     */
    private $currentCustomerAddress;
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * Info constructor.
     * @param Customerattribute $attributeHelper
     * @param CurrentCustomerAddress $currentCustomerAddress
     * @param Json $json
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        Customerattribute $attributeHelper,
        CurrentCustomerAddress $currentCustomerAddress,
        Json $json,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->attributeHelper = $attributeHelper;
        $this->currentCustomerAddress = $currentCustomerAddress;
        $this->json = $json;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param $customerAddressId
     * @return int |\Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomerAddressAttributeById($customerAddressId)
    {
        try {
            $addressAttribute = $this->addressRepository
                ->getById($customerAddressId)->getCustomAttributes();
            $customAddressAttribute = [];
            foreach ($addressAttribute as $attributeCode => $attribute) {
                $customAddressAttribute[$attributeCode] = $attribute->getValue();
            }
            return $customAddressAttribute;
        } catch (LocalizedException $e) {
            throw $e;
        }
        return false;
    }

    /**
     * @return B2BRegistrationIntegrationHelper
     */
    public function getAttributeHelper(): Customerattribute
    {
        return $this->attributeHelper;
    }

    /**
     * @return Json
     */
    public function getJson(): Json
    {
        return $this->json;
    }
    /**
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getShippingAddressAttribute()
    {
        return  $this->currentCustomerAddress->getDefaultShippingAddress();
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getBillingAddressAttribute()
    {
        return  $this->currentCustomerAddress->getDefaultBillingAddress();
    }
}
