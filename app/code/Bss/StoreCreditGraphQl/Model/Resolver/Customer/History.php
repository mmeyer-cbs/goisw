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
 * @package    Bss_StoreCreditGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCreditGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class Credit
 *
 * @package Bss\StoreCreditGraphQl\Model\Resolver
 */
class History implements ResolverInterface
{
    /**
     * @var \Bss\StoreCredit\Helper\Api
     */
    protected $helperApi;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    protected $historyCreditRepository;


    /**
     * Credit constructor.
     *
     * @param \Bss\StoreCredit\Helper\Api $helperApi
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Bss\StoreCredit\Api\HistoryRepositoryInterface $historyCreditRepository
     */
    public function __construct(
        \Bss\StoreCredit\Helper\Api $helperApi,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Bss\StoreCredit\Api\HistoryRepositoryInterface $historyCreditRepository
    ) {
        $this->helperApi = $helperApi;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->historyCreditRepository = $historyCreditRepository;
    }

    /**
     * Get Store Credit current of customer
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $customerId = $context->getUserId();
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter("customer_id", $customerId);
        $searchCriteria = $searchCriteriaBuilder->create();
        $historyCredit = $this->historyCreditRepository->getList($searchCriteria);
        return $this->helperApi->getHistoryItemFormatDate($historyCredit);
    }
}
