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
namespace Bss\CustomerAttributes\Plugin\Model\Customer;

use Magento\Customer\Api\Data\AddressInterface;
use Psr\Log\LoggerInterface;
use Bss\CustomerAttributes\Helper\Customer\Grid\NotDisplay;
use Bss\CustomerAttributes\Helper\Customerattribute;

/**
 * Class CustomerAddressDataProvider
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @package Bss\CustomerAttributes\Plugin\Model\Entity
 */
class Address
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var NotDisplay
     */
    protected $getAttr;

    /**
     * @var Customerattribute
     */
    protected $format;

    /**
     *  Address constructor.
     *
     * @param LoggerInterface $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param NotDisplay $getAttr
     * @param Customerattribute $format
     */
    public function __construct(
        LoggerInterface                 $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        NotDisplay                      $getAttr,
        Customerattribute               $format
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->getAttr = $getAttr;
        $this->format = $format;
    }

    /**
     * Save custom attribute address
     *
     * @param \Magento\Customer\Model\Address $subject
     * @param AddressInterface $customerAddress
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeUpdateData(
        $subject,
        AddressInterface $customerAddress
    ) {
        try {
            if (is_array($customerAddress->getCustomAttributes())
                && count($customerAddress->getCustomAttributes()) === 0) {
                $attributes = $this->checkoutSession->getCustomerAttributesAddress();
                if (is_array($attributes)) {
                    foreach ($attributes as $attribute) {
                        if (isset($attribute["value"]) && isset($attribute["attribute_code"])) {
                            $customerAddress->setCustomAttribute($attribute["attribute_code"], $attribute["value"]);
                        }
                    }
                }
            }
            foreach ($customerAddress->getCustomAttributes() as $key => $value) {
                $addressAttr = $this->getAttr->getAddressAttributeByCode($key);
                $addressAttrValue = $addressAttr->getData();
                if ($addressAttrValue && $addressAttrValue[0]['frontend_input'] === 'date') {
                    $value->setValue($this->format->formatDate($value->getValue()));
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return [$customerAddress];
    }
}
