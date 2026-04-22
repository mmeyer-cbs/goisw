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

use Bss\CompanyAccount\Model\SubUserOrderService;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SubUserPlaceOrder
 * Save sub-user info
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubUserPlaceOrder
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SubUserOrderService
     */
    protected $userOrderService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var GetCartForUser
     */
    protected $getCartForUser;

    /**
     * SubUserPlaceOrder constructor.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubUserOrderService $userOrderService
     * @param LoggerInterface $logger
     * @param GetCartForUser $getCartForUser
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        SubUserOrderService $userOrderService,
        LoggerInterface $logger,
        GetCartForUser $getCartForUser
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->userOrderService = $userOrderService;
        $this->logger = $logger;
        $this->getCartForUser = $getCartForUser;
    }

    /**
     * Validate sub-user can place order
     *
     * @param PlaceOrder $subject
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws \Bss\CompanyAccountGraphQl\Exception\GraphQlSubUserAccessException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function beforeResolve(
        PlaceOrder $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($context->getExtensionAttributes()->getIsSubUser()) {
            if (empty($args['input']['cart_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
            }
            $maskedCartId = $args['input']['cart_id'];

            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
            $this->userOrderService->canSubUserOrder(
                $context->getExtensionAttributes()->getSubUserId(),
                $cart
            );
        }

        return [$field, $context, $info, $value, $args];
    }

    /**
     * Save sub-user info if auth is subuser
     *
     * @param PlaceOrder $subject
     * @param $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function afterResolve(
        PlaceOrder $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$context->getExtensionAttributes()->getIsSubUser() ||
            !isset($result['order']['order_number'])
        ) {
            return $result;
        }

        $incrementId = $result['order']['order_number'];
        $this->searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, $incrementId);
        $orders = $this->orderRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();

        foreach ($orders as $order) {
            try {
                $this->userOrderService->assignSubUserToOrder(
                    $context->getExtensionAttributes()->getSubUserId(),
                    $order
                );
            } catch (\Exception $e) {
                $this->logger->critical(
                    __("BSS.ERROR: Failed when assign sub-user to order. %1", $e)
                );
            }
            break;
        }

        return $result;
    }
}
