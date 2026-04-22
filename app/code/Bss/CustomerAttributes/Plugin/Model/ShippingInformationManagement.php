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
namespace Bss\CustomerAttributes\Plugin\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\QuoteAddressValidator;

/**
 * Class ShippingInformationManagement
 * @package Bss\CustomerAttributes\Model
 */
class ShippingInformationManagement
{
    protected $quoteRepository;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var QuoteAddressValidator
     */
    private $addressValidator;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    private $helper;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        QuoteAddressValidator $addressValidator,
        Json $json,
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
        $this->addressValidator = $addressValidator;
        $this->json = $json;
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Before save address information
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param string $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $customerAddId = $addressInformation->getShippingAddress()->getCustomerAddressId();
        $customerAddressField = $addressInformation->getShippingAddress()->getExtensionAttributes()->getCustomField();
        if ($customerAddressField) {
            $customField = $this->json->unserialize($customerAddressField);
            $customAddress = [];
            foreach ($customField as $item) {
                if ($item['value'] !=='') {
                    $customAddress[$item['attribute_code']] = $item['value'];
                }
            }
            if (!$customerAddId) {
                if (!empty($customAddress)) {
                    $quote = $this->quoteRepository->getActive($cartId);
                    $billingAddress = $addressInformation->getBillingAddress();
                    if ($billingAddress) {
                        if (!$billingAddress->getCustomerAddressId()) {
                            $billingAddress->setCustomerAddressId(null);
                        }
                        $this->addressValidator->validateForCart($quote, $billingAddress);
                        $quote->setBillingAddress($billingAddress);
                        $quote->getBillingAddress()->addData($customAddress);
                        $quote->getBillingAddress()->setData('customer_address_attribute', $customerAddressField);
                    }
                    $quote->getShippingAddress()->addData($customAddress);
                    $quote->getShippingAddress()->setData('customer_address_attribute', $customerAddressField);
                    $quote->setDataChanges(true);
                    $this->quoteRepository->save($quote);
                }
            } else {
                if (!empty($customAddress)) {
                    $quote = $this->quoteRepository->getActive($cartId);
                    $quote->getShippingAddress()->addData($customAddress);
                    $quote->getShippingAddress()->setData('customer_address_attribute', $customerAddressField);
                    $quote->setDataChanges(true);
                    $this->quoteRepository->save($quote);
                }
            }
        }
    }
}
