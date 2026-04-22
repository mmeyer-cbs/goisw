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
namespace Bss\CustomerAttributes\Observer\Checkout;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ShippingPost
 *
 */
class ShippingPost implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    private $helper;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * ShippingPost constructor.
     * @param AddressRepositoryInterface $addressRepository
     * @param Json $json
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        Json $json,
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    ) {
        $this->addressRepository = $addressRepository;
        $this->json = $json;
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $customerAddressId = $quote->getShippingAddress()->getCustomerAddressId();
        $addresses = $this->addressRepository
            ->getById($customerAddressId)->getCustomAttributes();
        $customAddressAttribute = [];
        foreach ($addresses as $attributeCode => $attribute) {
            $addressAttribute = $this->attributeRepository
                ->get('customer_address', $attributeCode);
            $addressValue = $this->helper->getValueAddressAttributeForOrder(
                $addressAttribute,
                $attribute->getValue()
            );
            $value = [
                'value' => $addressValue,
                'label' => $addressAttribute->getFrontendLabel()
            ];
            $customAddressAttribute[$attributeCode] = $value;
        }
        $jsonAddress = $this->json->serialize($customAddressAttribute);
        $quote->getShippingAddress()->setCustomerAddressAttribute($jsonAddress);
        $quote->setDataChanges(true);
        return $this;
    }
}
