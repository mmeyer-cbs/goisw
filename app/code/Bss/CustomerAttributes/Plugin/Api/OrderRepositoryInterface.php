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

namespace Bss\CustomerAttributes\Plugin\Api;

use Bss\CustomerAttributes\Helper\Data;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderAddressExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\Data\ShippingExtensionInterfaceFactory;

/**
 * Class OrderRepositoryInterface
 */
class OrderRepositoryInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ShippingExtensionInterfaceFactory
     */
    protected $shippingExtensionInterfaceFactory;

    /**
     * @var OrderAddressExtensionInterfaceFactory
     */
    private $addressExtensionInterfaceFactory;

    /**
     * @var AttributeValueFactory
     */
    protected $attributeValue;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * OrderRepositoryInterface constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param ShippingExtensionInterfaceFactory $shippingExtensionInterfaceFactory
     * @param OrderAddressExtensionInterfaceFactory $addressExtensionInterfaceFactory
     * @param AttributeValueFactory $attributeValue
     * @param Data $helper
     * @param Json $json
     */
    public function __construct(
        \Psr\Log\LoggerInterface              $logger,
        ShippingExtensionInterfaceFactory     $shippingExtensionInterfaceFactory,
        OrderAddressExtensionInterfaceFactory $addressExtensionInterfaceFactory,
        AttributeValueFactory                 $attributeValue,
        Data                                  $helper,
        Json                                  $json
    ) {
        $this->logger = $logger;
        $this->shippingExtensionInterfaceFactory = $shippingExtensionInterfaceFactory;
        $this->addressExtensionInterfaceFactory = $addressExtensionInterfaceFactory;
        $this->attributeValue = $attributeValue->create();
        $this->helper = $helper;
        $this->json = $json;
    }

    /**
     * Add extension attribute into order API
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet($subject, OrderInterface $order)
    {
        if ($this->helper->isEnable()) {
            $this->setExtensionShippingAddress($order);
            $this->setExtensionAttributes($order);
            $this->setExtensionBillingAddress($order);
        }
        return $order;
    }

    /**
     * Add extension attribute into orders API
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $result
     * @param SearchCriteriaInterface $searchCriteria
     * @return OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        $subject,
        OrderSearchResultInterface $result,
        SearchCriteriaInterface $searchCriteria
    ) {
        if ($this->helper->isEnable()) {
            $items = $result->getItems();
            foreach ($items as $item) {
                $this->setExtensionShippingAddress($item);
                $this->setExtensionBillingAddress($item);
                $this->setExtensionAttributes($item);
            }
        }
        return $result;
    }

    /**
     * Add customer attributes into extension_attributes
     *
     * @param OrderInterface $order
     */
    public function setExtensionAttributes($order)
    {
        try {
            if ($customerAttributes = $order->getCustomerAttribute()) {
                $customerAttributes = $this->json->unserialize($customerAttributes);
                $extensionAttributes = $order->getExtensionAttributes();
                if (count($customerAttributes) && $extensionAttributes) {
                    $extensionAttributesCustomerAttributes = [];
                    foreach ($customerAttributes as $attributeCode => $value) {
                        $this->attributeValue->setValue($value);
                        $this->attributeValue->setAttributeCode($attributeCode);
                        $extensionAttributesCustomerAttributes[] = $this->attributeValue;
                    }
                    $extensionAttributes->setCustomerAttribute($extensionAttributesCustomerAttributes);
                    $order->setExtensionAttributes($extensionAttributes);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * Add customer attributes into extension_attributes
     *
     * @param OrderAddressInterface $orderAddress
     */
    public function getDataExtensionAttributesAddress($orderAddress)
    {
        if ($customerAttributes = $orderAddress->getCustomerAddressAttribute()) {
            $customerAttributes = $this->json->unserialize($customerAttributes);
            $extensionAttributesCustomerAttributes = [];
            foreach ($customerAttributes as $attributeCode => $value) {
                $extensionAttributesCustomerAttributes[][$attributeCode] = $value;
            }
            return $extensionAttributesCustomerAttributes;
        }
        return null;
    }

    /**
     * Add custom address attributes into billing address extension_attributes
     *
     * @param OrderInterface $order
     */
    public function setExtensionBillingAddress($order)
    {
        try {
            /**
             * @var OrderAddressInterface $billingAddress
             */
            $billingAddress = $order->getBillingAddress();
            $extensionAttributes = $this->getDataExtensionAttributesAddress($billingAddress);
            if ($extensionAttributes) {
                $billingAddressExtensionAttributes = (null !== $billingAddress->getExtensionAttributes()) ?
                    $billingAddress->getExtensionAttributes() :
                    $this->addressExtensionInterfaceFactory->create();
                $billingAddressExtensionAttributes->setCustomAddress($extensionAttributes);
                $billingAddress->setExtensionAttributes($billingAddressExtensionAttributes);
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * Add custom address attributes into shipping address extension_attributes
     *
     * @param OrderInterface $order
     */
    public function setExtensionShippingAddress($order)
    {
        try {
            if (!$order->getIsVirtual()) {
                $shippingAssignments = $order->getExtensionAttributes()->getShippingAssignments();
                if ($shippingAssignments) {
                    foreach ($shippingAssignments as $shippingAssignment) {
                        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
                        $extensionAttributes = $this->getDataExtensionAttributesAddress($shippingAddress);
                        if ($extensionAttributes) {
                            $shippingAddressExtensionAttributes = (null !== $shippingAddress->getExtensionAttributes()) ?
                                $shippingAddress->getExtensionAttributes() :
                                $this->addressExtensionInterfaceFactory->create();
                            $shippingAddressExtensionAttributes->setCustomAddress($extensionAttributes);
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
