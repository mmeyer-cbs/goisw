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
 * @package    Bss_CompanyAccountGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccountGraphQl\Plugin;

use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccountGraphQl\Exception\GraphQlSubUserAccessException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;
use Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query\OrderFilter;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Rebuild customer orders if sub-user be auth
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerOrders extends SubUserActionAbstract
{
    const PERMISSION = Permissions::VIEW_ALL_ORDER;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderFilter
     */
    protected $orderFilter;

    /**
     * @var OrderFormatter
     */
    protected $orderFormatter;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    protected $userOrderRepository;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * CustomerOrders constructor
     *
     * @param PermissionsChecker $permissionsChecker
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderFilter $orderFilter
     * @param OrderFormatter $orderFormatter
     * @param SubUserOrderRepositoryInterface $userOrderRepository
     * @param SubUserRepositoryInterface $subUserRepository
     */
    public function __construct(
        PermissionsChecker $permissionsChecker,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderFilter $orderFilter,
        OrderFormatter $orderFormatter,
        SubUserOrderRepositoryInterface $userOrderRepository,
        SubUserRepositoryInterface $subUserRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderFilter = $orderFilter;
        $this->orderFormatter = $orderFormatter;
        $this->userOrderRepository = $userOrderRepository;
        $this->subUserRepository = $subUserRepository;
        parent::__construct($permissionsChecker);
    }

    /**
     * Get list order for sub-user if sub-user be authenticated
     *
     * @param \Magento\SalesGraphQl\Model\Resolver\CustomerOrders $subject
     * @param callable $proceed
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException|GraphQlInputException|GraphQlAuthorizationException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function aroundResolve(
        $subject,
        callable $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $this->isAllowed($context);
        } catch (GraphQlSubUserAccessException $e) {
            if (false === $context->getExtensionAttributes()->getIsCustomer()) {
                throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
            }
            if ($args['currentPage'] < 1) {
                throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
            }
            if ($args['pageSize'] < 1) {
                throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
            }
            $userId = $context->getUserId();
            /** @var StoreInterface $store */
            $store = $context->getExtensionAttributes()->getStore();
            try {
                $searchResult = $this->getSearchResult(
                    $args,
                    (int)$userId,
                    (int)$store->getId(),
                    $context->getExtensionAttributes()->getSubUserId()
                );
                $maxPages = (int)ceil($searchResult->getTotalCount() / $searchResult->getPageSize());
            } catch (InputException $e) {
                throw new GraphQlInputException(__($e->getMessage()));
            }

            $ordersArray = [];
            foreach ($searchResult->getItems() as $orderModel) {
                $ordersArray[] = $this->orderFormatter->format($orderModel);
            }

            return [
                'total_count' => $searchResult->getTotalCount(),
                'items' => $ordersArray,
                'page_info' => [
                    'page_size' => $searchResult->getPageSize(),
                    'current_page' => $searchResult->getCurPage(),
                    'total_pages' => $maxPages,
                ]
            ];
        }

        return $proceed($field, $context, $info, $value, $args);
    }

    /**
     * Get search result from graphql query arguments and subuser id
     *
     * @param array $args
     * @param int $userId
     * @param int $storeId
     * @param int $subUserId
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     * @throws InputException
     */
    protected function getSearchResult(array $args, int $userId, int $storeId, int $subUserId)
    {
        $filterGroups = $this->orderFilter->createFilterGroups($args, $userId, (int)$storeId);
        $this->searchCriteriaBuilder->setFilterGroups($filterGroups);
        if (isset($args['currentPage'])) {
            $this->searchCriteriaBuilder->setCurrentPage($args['currentPage']);
        }
        if (isset($args['pageSize'])) {
            $this->searchCriteriaBuilder->setPageSize($args['pageSize']);
        }

        $this->searchCriteriaBuilder->addFilter(
            'entity_id',
            $this->userOrderRepository->getBySubUser($subUserId),
            'in'
        );

        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }
}
