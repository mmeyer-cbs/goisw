<?php
declare(strict_types=1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\Ui\Component\DataProvider;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Ui\Component\DataProvider\Document as BePlugged;
use Psr\Log\LoggerInterface;
/**
 * Class Document
 * Set is company account attribute value
 * @see BePlugged
 */
class Document
{
    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Document constructor.
     *
     * @param CustomerMetadataInterface $customerMetadata
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadata,
        LoggerInterface $logger
    ) {
        $this->customerMetadata = $customerMetadata;
        $this->logger = $logger;
    }

    /**
     * Set is company account attribute value
     *
     * @param BePlugged $subject
     * @param callable $proceed
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface
     */
    public function aroundGetCustomAttribute(
        BePlugged $subject,
        callable $proceed,
        $attributeCode
    ) {
        if ($attributeCode === "bss_is_company_account") {
            $this->setIsCompanyAccountValue($subject, $attributeCode);
        }

        return $proceed($attributeCode);
    }

    /**
     * Set is company account attribute label instead value
     *
     * @param BePlugged $subject
     * @param string $attributeCode
     */
    protected function setIsCompanyAccountValue(BePlugged $subject, $attributeCode)
    {
        $value = $subject->getData($attributeCode);

        if (!$value) {
            $subject->setCustomAttribute($attributeCode, __("No"));
            return;
        }

        try {
            $attributeMetadata = $this->customerMetadata->getAttributeMetadata($attributeCode);

            foreach ($attributeMetadata->getOptions() as $option) {
                if ($option->getValue() == $value) {
                    $attributeOption = $option;
                }
            }
            if (!isset($attributeOption)) {
                $subject->setCustomAttribute($attributeCode, __("No"));
                return;
            }

            $subject->setCustomAttribute($attributeCode, $attributeOption->getLabel());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $subject->setCustomAttribute($attributeCode, __("No"));
        }
    }
}
