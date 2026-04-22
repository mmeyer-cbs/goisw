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

use Bss\CustomerAttributes\Helper\Customer\Grid\NotDisplay;
use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PaymentInformationManagement
{

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var Customerattribute
     */
    private $helper;
    /**
     * @var Json
     */
    private $json;

    /**
     * @var NotDisplay
     */
    protected $getAttr;

    /**
     * PaymentInformationManagement constructor.
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param CartRepositoryInterface $cartRepository
     * @param Customerattribute $helper
     * @param NotDisplay $getAttr
     * @param Json $json
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        CartRepositoryInterface    $cartRepository,
        Customerattribute          $helper,
        NotDisplay                 $getAttr,
        Json                       $json
    ) {
        $this->addressRepository = $addressRepository;
        $this->cartRepository = $cartRepository;
        $this->helper = $helper;
        $this->getAttr = $getAttr;
        $this->json = $json;
    }

    /**
     * Set data custom address attributes for billing address and shipping adress
     *
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param bool $result
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
                                                             $result,
                                                             $cartId,
        PaymentInterface                                     $paymentMethod,
        AddressInterface                                     $billingAddress = null
    )
    {
        if ($this->helper->isEnable()) {
            $quoteRepository = $this->cartRepository;
            /** @var Quote $quote */
            $quote = $quoteRepository->getActive($cartId);
            if ($billingAddress) {
                $customerAddId = $billingAddress->getCustomerAddressId();
                if ($customerAddId) {
                    try {
                        $customAttribute = $this->addressRepository
                            ->getById($customerAddId)->getCustomAttributes();
                    } catch (LocalizedException $e) {
                        throw $e;
                    }
                    if (!empty($customAttribute)) {
                        $customAddress = [];
                        foreach ($customAttribute as $key => $attribute) {
                            if ($this->helper->isVisible($attribute->getAttributeCode())) {
                                $addressAttr = $this->getAttr->getAddressAttributeByCode(
                                    $attribute->getAttributeCode()
                                );
                                $addressAttrValue = $addressAttr->getData();
                                if ($addressAttrValue && $addressAttrValue[0]['frontend_input'] === 'date') {
                                    $attribute->setValue(
                                        $this->helper->formatDate($attribute->getValue())
                                    );
                                }
                                $customAddress[$key] = $attribute->getValue();
                            }
                        }
                        $quote->getBillingAddress()->addData($customAddress);
                        if (!$quote->isVirtual()) {
                            $this->setCustomShippingAddress($quote);
                        }
                        $quote->setDataChanges(true);
                    }
                } elseif ($billingAddress) {
                    $customerAddressField = $billingAddress->getExtensionAttributes()->getCustomField();
                    if ($customerAddressField !== null) {
                        $quote->getBillingAddress()->setData('customer_address_attribute', $customerAddressField);
                        $quote->setDataChanges(true);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Set data custom address attributes for billing address
     *
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    public function setCustomShippingAddress($quote)
    {
        if ($addressId = $quote->getShippingAddress()->getCustomerAddressId()) {
            try {
                $customAttributeForShipping = $this->addressRepository
                    ->getById($addressId)->getCustomAttributes();
            } catch (\Exception $exception) {
                throw  $exception;
            }
            foreach ($customAttributeForShipping as $key => $attribute) {
                if ($this->helper->isVisible($attribute->getAttributeCode())
                ) {
                    $addressAttr = $this->getAttr->getAddressAttributeByCode(
                        $attribute->getAttributeCode()
                    );
                    $addressAttrValue = $addressAttr->getData();
                    if ($addressAttrValue && $addressAttrValue[0]['frontend_input'] === 'date') {
                        $attribute->setValue(
                            $this->helper->formatDate($attribute->getValue())
                        );
                    }
                    $customShippingAddress[$key] = $attribute->getValue();
                }
            }
            if (!empty($customShippingAddress)) {
                $quote->getShippingAddress()->addData($customShippingAddress);
            }
        }
    }
}
