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

namespace Bss\CustomerAttributes\Plugin\Model\Customer\Address;

use Bss\CustomerAttributes\Helper\Customer\Grid\NotDisplay;
use Bss\CustomerAttributes\Helper\Customerattribute;
use Exception;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerAddressDataProvider
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @package Bss\CustomerAttributes\Plugin\Model\Entity
 */
class CustomerAddressDataProvider
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Customerattribute
     */
    protected $helperCustomerAttributes;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var NotDisplay
     */
    protected $getAttr;

    /**
     * Type constructor.
     *
     * @param LoggerInterface $logger
     * @param Customerattribute $helperCustomerAttributes
     * @param AttributeRepositoryInterface $attributeRepository
     * @param NotDisplay $getAttr
     */
    public function __construct(
        LoggerInterface              $logger,
        Customerattribute            $helperCustomerAttributes,
        AttributeRepositoryInterface $attributeRepository,
        NotDisplay                   $getAttr
    ) {
        $this->logger = $logger;
        $this->helperCustomerAttributes = $helperCustomerAttributes;
        $this->attributeRepository = $attributeRepository;
        $this->getAttr = $getAttr;
    }

    /**
     * Get Label
     *
     * @param string $attributeCode
     * @param array $attributeValue
     * @return false|string
     */
    public function getLable($attributeCode, $attributeValue)
    {
        try {
            if (!$this->helperCustomerAttributes->isCustomAddressAttribute($attributeCode)) {
                return false;
            }
            $addressAttribute = $this->attributeRepository
                ->get('customer_address', $attributeCode);
            $frontendInput = $addressAttribute->getFrontendInput();
            if ($frontendInput == 'text' ||
                $frontendInput == 'textarea' ||
                $frontendInput == 'file' ||
                $frontendInput == 'date'
            ) {
                return false;
            }
            if ($frontendInput == "file" && $attributeValue["value"]) {
                $label = $this->getFileName($attributeValue["value"]);
            } else {
                $label = $this->helperCustomerAttributes->getValueAddressAttributeForOrder(
                    $addressAttribute,
                    $attributeValue
                );
            }
            return $label;
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return false;
    }

    /**
     * Fix display label customer address
     *
     * @param \Magento\Customer\Model\Address\CustomerAddressDataProvider $subject
     * @param array $result
     * @return mixed
     */
    public function afterGetAddressDataByCustomer($subject, $addresses)
    {
        try {
            if (is_array($addresses)) {
                foreach ($addresses as $key => $address) {
                    if (isset($address["custom_attributes"]) && is_array($address["custom_attributes"])) {
                        $customAttributes = $address["custom_attributes"];
                        foreach ($customAttributes as $attributeCode => $attributeValue) {
                            $label = $this->getLable($attributeCode, $attributeValue);
                            $addressAttr = $this->getAttr->getAddressAttributeByCode($attributeCode);
                            $addressAttrValue = $addressAttr->getData();
                            if ($addressAttrValue && $addressAttrValue[0]['frontend_input'] === 'date') {
                                $customAttributes[$attributeCode]['value'] = (
                                $this->helperCustomerAttributes->formatDate($attributeValue['value'])
                                );
                            }
                            if ($label) {
                                $customAttributes[$attributeCode]["label"] = $label;
                            }
                        }
                        $addresses[$key]["custom_attributes"] = $customAttributes;
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $addresses;
    }

    /**
     * Get file name
     *
     * @param string $filename
     * @return string
     */
    public function getFileName($filename)
    {
        if ($filename && strpos($filename, "/") !== false) {
            $nameArr = explode("/", $filename);
            return end($nameArr);
        }
        return $filename;
    }
}
