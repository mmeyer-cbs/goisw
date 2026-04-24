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
namespace Bss\CustomerAttributes\Plugin\Model\Customer\Attribute\Validator;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Api\AttributeInterface;

/**
 * Class File
 *
 * @package Bss\CustomerAttributes\Plugin\Model\Customer\Attribute\Validator
 */
class File
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * File constructor.
     *
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Psr\Log\LoggerInterface $logger
     * @param EavConfig $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Psr\Log\LoggerInterface $logger,
        EavConfig $eavConfig
    ) {
        $this->productMetadata = $productMetadata;
        $this->logger = $logger;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Validate file
     *
     * @param \Magento\Customer\Model\Customer\Attribute\Validator\File $subject
     * @param callable $proceed
     * @param AttributeInterface $customAttribute
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate($subject, callable $proceed, $customAttribute)
    {
        try {
            $versionMagento = $this->productMetadata->getVersion();
            if ($versionMagento == "2.3.6" || $versionMagento == "2.3.7") {
                $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, $customAttribute->getAttributeCode());
                if ($attribute->getFrontendInput() === 'file'
                    && gettype($customAttribute->getValue()) == 'boolean'
                ) {
                    return true;
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $proceed($customAttribute);
    }

}
