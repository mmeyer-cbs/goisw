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

namespace Bss\CustomPricing\Model\Config\Source;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class CustomerGroups data source
 */
class CustomerGroups
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * CustomerGroups constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupRepositoryInterface $customerGroupRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $customerGroupRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * Get customer group array data
     *
     * @return array
     */
    public function toOptionArray()
    {
        $groups = $this->getCustomerGroups();
        $groupArrayData = [];
        foreach ($groups as $group) {
            $groupArrayData[] = [
                "label" => $group->getCode(),
                "value" => $group->getId()
            ];
        }
        if (isset($groupArrayData[0])) {
            unset($groupArrayData[0]);
        }
        return $groupArrayData;
    }

    /**
     * Get customer group option array
     *
     * @return array
     */
    public function getHashOptionArray()
    {
        $groups = $this->getCustomerGroups();
        $groupArrayData = [];
        foreach ($groups as $group) {
            $groupArrayData[$group->getId()] = $group->getCode();
        }
        return $groupArrayData;
    }

    /**
     * Get customer groups
     *
     * @return array|\Magento\Customer\Api\Data\GroupInterface[]
     */
    protected function getCustomerGroups()
    {
        try {
            return $this->customerGroupRepository
                ->getList(
                    $this->searchCriteriaBuilder->create()
                )->getItems();
        } catch (\Exception $e) {
            return [];
        }
    }
}
