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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\Rule\Condition\Customer;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Rule\Model\Condition\Context;
use Bss\CustomPricing\Model\Config\Source\CustomerAttributes;

/**
 * Class Combine for customer conditions
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    protected $elementName = 'general_information[rule]';

    /**
     * @var CustomerAttributes
     */
    protected $customerAttributeDataSource;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupRepositoryInterface $cGroupRepository
     * @param CustomerAttributes $customerAttributeDataSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $cGroupRepository,
        CustomerAttributes $customerAttributeDataSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->groupRepository = $cGroupRepository;
        $this->setType(Combine::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerAttributeDataSource = $customerAttributeDataSource;
    }

    /**
     * @inheritDoc
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        try {
            return array_merge_recursive(
                $conditions,
                [
                    [
                        'value' => Combine::class,
                        'label' => __('Conditions Combination'),
                    ],
                    [
                        'label' => __("Customer"),
                        'value' => $this->getCustomerCondOptions()
                    ],
                    [
                        'label' => __("Customer Attributes"),
                        'value' => $this->getCustomerAttributeCondOptions()
                    ]
                ]
            );
        } catch (\Exception $e) {
            return array_merge_recursive(
                $conditions,
                [
                    [
                        'value' => Combine::class,
                        'label' => __('Conditions Combination'),
                    ]
                ]
            );
        }
    }

    /**
     * Get customer conditions option
     *
     * @return array[]
     */
    private function getCustomerCondOptions()
    {
        return [
            [
                "value" => "Bss\CustomPricing\Model\Rule\Condition\Customer\Customer|groups",
                "label" => __("Customer Group")
            ],
            [
                "value" => "Bss\CustomPricing\Model\Rule\Condition\Customer\Customer|specified",
                "label" => __("Specified Customers")
            ]
        ];
    }

    /**
     * Get customer conditions option
     *
     * @return array[]
     */
    private function getCustomerAttributeCondOptions()
    {
        $attributes = [];
        $customerAttributes = $this->customerAttributeDataSource->getCustomerAttributes();
        foreach ($customerAttributes as $attribute) {
            $attributes[] = [
                'value' => 'Bss\CustomPricing\Model\Rule\Condition\Customer\Customer|' . $attribute->getAttributeCode(),
                'label' => $attribute->getAttributeCode(),
            ];
        }
        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function getConditions()
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'customer';
        return $this->getData($key);
    }

    /**
     * @inheritDoc
     */
    public function setConditions($conditions)
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'customer';
        return $this->setData($key, $conditions);
    }

    /**
     * @param array $customerCollection
     * @return $this
     */
    public function collectValidatedAttributes($customerCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Customer|Combine $condition */
            $condition->collectValidatedAttributes($customerCollection);
        }
        return $this;
    }
}
