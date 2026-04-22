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

namespace Bss\CustomerAttributes\Observer;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class CustomerAttrSave implements ObserverInterface
{
    /**
     * @var array
     */
    protected $attributesArrays = [];

    /**
     * @var array
     */
    protected $addressAttributesArrays = [];

    /**
     * @var GuestToCustomer\Helper\Observer\Helper
     */
    protected $customerFactory;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Entity
     */
    protected $entityModel;

    /**
     * @var Customerattribute
     */
    protected $helper;

    /**
     * @var CustomerMetadataInterface
     */
    protected $metadata;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CustomerAttrSave constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Session $checkoutSession
     * @param Entity $entityModel
     * @param Customerattribute $helper
     * @param CustomerMetadataInterface $metadata
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Session                                 $checkoutSession,
        Entity                                  $entityModel,
        Customerattribute                       $helper,
        CustomerMetadataInterface               $metadata,
        Json                                    $json,
        LoggerInterface                         $logger
    ) {
        $this->customerFactory = $customerFactory;
        $this->checkoutSession = $checkoutSession;
        $this->entityModel = $entityModel;
        $this->helper = $helper;
        $this->metadata = $metadata;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * @param EventObserver $observer
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getCustomerId()) {
            $this->saveForCustomer($order);
        } else {
            $this->saveForGuest();
        }
        if (!empty($this->attributesArrays)) {
            $order->setCustomerAttribute($this->json->serialize($this->attributesArrays));
        }
        try {
            $order->save();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * Save For Customer
     *
     * @param Order $order
     * @throws LocalizedException
     */
    private function saveForCustomer($order)
    {
        $customer = $this->customerFactory->create()->load($order->getCustomerId());
        $customerAttr = $this->checkoutSession->getCustomerAttributes();
        if (!empty($customerAttr)) {
            $this->checkoutSession->unsCustomerAttributes();
            $customerData = $customer->getDataModel();
            foreach ($customerAttr as $attr => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $customerData->setCustomAttribute($attr, $value);
            }
            $customer->updateData($customerData);
            try {
                $customer->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        //        set address attribute array
        $customBillingAddress = $order->getBillingAddress()->getCustomerAddressAttribute();
        if ($customBillingAddress && is_array($customBillingAddress)) {
            $order->getBillingAddress()->setCustomerAddressAttribute($this->json->serialize($customBillingAddress));
        }
        if (!$order->getIsVirtual()) {
            $customShippingAddress = $order->getShippingAddress()->getCustomerAddressAttribute();
            if ($customShippingAddress && is_array($customShippingAddress)) {
                $order->getShippingAddress()->setCustomerAddressAttribute(
                    $this->json->serialize($customShippingAddress)
                );
            }
        }
        $entityTypeId = 'customer';
        $attributes = $this->metadata->getAllAttributesMetadata($entityTypeId);
        foreach ($attributes as $attribute) {
            if ($attribute->isSystem() || !$attribute->isUserDefined()) {
                continue;
            }
            if ($attribute->getFrontendInput() == 'date') {
                if ($attribute->getDefaultValue()) {
                    $format = str_replace(" 00:00:00", "", $attribute->getDefaultValue());
                } else {
                    $format = '';
                }
                $attribute->setDefaultValue($format);
            }
            $this->getAttributesArray($attribute, $customer->getDataModel());
        }
    }

    /**
     * @throws LocalizedException
     */
    private function saveForGuest()
    {
        $customerAttr = $this->checkoutSession->getCustomerAttributes();
        if (!empty($customerAttr)) {
            $customerAttrSession = [];
            foreach ($customerAttr as $attr => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $customerAttrSession[$attr] = $value;
            }
            $entityTypeId = 'customer';
            $attributes = $this->metadata->getAllAttributesMetadata($entityTypeId);
            foreach ($attributes as $attribute) {
                if ($attribute->isSystem() || !$attribute->isUserDefined()) {
                    continue;
                }
                if ($attribute->getFrontendInput() == 'date') {
                    if ($attribute->getDefaultValue()) {
                        $format = str_replace(" 00:00:00", "", $attribute->getDefaultValue());
                    } else {
                        $format = '';
                    }
                    $attribute->setDefaultValue($format);
                }
                $this->getAttributesArrayForGuest($attribute, $customerAttrSession);
            }
        }
    }

    /**
     * @param Attribute $attribute
     * @param Customer $customer
     * @throws LocalizedException
     */
    private function getAttributesArray($attribute, $customer)
    {
        if ($this->helper->isAttribureForOrderDetail($attribute->getAttributeCode(), $customer->getId())) {
            $customAttribute = $customer->getCustomAttribute($attribute->getAttributeCode());
            if ($customAttribute != '' && $customAttribute->getValue() != '') {
                $this->attributesArrays[$attribute->getAttributeCode()] = $customAttribute->getValue();
            }
        }
    }

    /**
     * @param Attribute $attribute
     * @param array $customerAttrSession
     * @throws LocalizedException
     */
    private function getAttributesArrayForGuest($attribute, $customerAttrSession)
    {
        if ($this->helper->isAttribureForOrderDetail($attribute->getAttributeCode())) {
            if (isset($customerAttrSession[$attribute->getAttributeCode()]) &&
                $customerAttrSession[$attribute->getAttributeCode()] != '') {
                $this->attributesArrays[$attribute->getAttributeCode()]
                    = $customerAttrSession[$attribute->getAttributeCode()];
            }
        }
    }
}
