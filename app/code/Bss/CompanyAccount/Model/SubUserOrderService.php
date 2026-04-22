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

namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccountGraphQl\Exception\GraphQlSubUserAccessException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Provide functions for sub-users and orders
 */
class SubUserOrderService
{
    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    protected $subUserOrderRepository;

    /**
     * @var SubUserOrderFactory
     */
    protected $subUserOrderFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * SubUserOrderService constructor.
     *
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubUserOrderRepositoryInterface $subUserOrderRepository
     * @param SubUserOrderFactory $subUserOrderFactory
     * @param SerializerInterface $serializer
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param LoggerInterface $logger
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(
        SubUserRepositoryInterface $subUserRepository,
        SubUserOrderRepositoryInterface $subUserOrderRepository,
        SubUserOrderFactory $subUserOrderFactory,
        SerializerInterface $serializer,
        SearchCriteriaBuilder $criteriaBuilder,
        LoggerInterface $logger,
        PermissionsChecker $permissionsChecker
    ) {
        $this->subUserRepository = $subUserRepository;
        $this->subUserOrderRepository = $subUserOrderRepository;
        $this->subUserOrderFactory = $subUserOrderFactory;
        $this->serializer = $serializer;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->logger = $logger;
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * @param mixed $subUser
     * @param OrderInterface $order
     * @throws NoSuchEntityException|CouldNotSaveException
     * @since v1.0.7
     */
    public function assignSubUserToOrder($subUser, OrderInterface $order)
    {
        if (!$subUser instanceof SubUserInterface) {
            $subUser = $this->subUserRepository->getById($subUser);
        }

        $hasExisted = $this->isOrderHasAssignedSubUser(
            (int) $subUser->getSubId(),
            (int) $order->getEntityId()
        );
        if ($hasExisted) {
            return;
        }

        $orderId = $order->getEntityId();
        $userOrder = $this->subUserOrderFactory->create();
        $userOrder->setSubId($subUser->getSubId());
        $userOrder->setOrderId($orderId);
        $userOrder->setGrandTotal($order->getBaseGrandTotal());
        $subUserInfo[SubUserInterface::NAME] = $subUser->getSubName();
        $subUserInfo[SubUserInterface::EMAIL] = $subUser->getSubEmail();
        $subUserInfo['role_name'] = $subUser->getData('role_name');
        $subUserInfo['order_request'] = $subUser->getQuoteId();
        $userOrder->setSubUserInfo(
            $this->serializer->serialize($subUserInfo)
        );
        $this->subUserOrderRepository->save($userOrder);
    }

    /**
     * Check exits sub user order
     *
     * @param int $subUserId
     * @param int $orderId
     * @return bool
     */
    public function isOrderHasAssignedSubUser($subUserId, $orderId): bool
    {
        try {
            $subUsers = $this->subUserOrderRepository->getList(
                $this->criteriaBuilder
                    ->addFilter('sub_id', $subUserId)
                    ->addFilter('order_id', $orderId)
                    ->create())
                ->getItems();
            if (count($subUsers) > 0) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return true;
        }
    }

    /**
     * @param int $subUserId
     * @param CartInterface $cart
     * @return bool
     * @throws GraphQlSubUserAccessException
     */
    public function canSubUserOrder($subUserId, CartInterface $cart): bool
    {
        try {
            if ($this->permissionsChecker->isAdmin()) {
                return true;
            }

            $cantAccessWithOrderAmount = $this->permissionsChecker
                ->canSubUserAccess($subUserId, Permissions::MAX_ORDER_AMOUNT, $cart->getBaseSubtotal());
            $cantAccessWithOrderPerDay = $this->permissionsChecker
                ->canSubUserAccess($subUserId, Permissions::MAX_ORDER_PERDAY);

            if ($cantAccessWithOrderAmount['is_denied']) {
                throw new GraphQlSubUserAccessException(
                    __(
                        'You just can checkout cart with amount less than %1',
                        $cantAccessWithOrderAmount['accessible_value']
                    )
                );
            }

            if ($cantAccessWithOrderPerDay['is_denied']) {
                throw new GraphQlSubUserAccessException(
                    __(
                        'You have reached the maximum (%1) number of order perday.',
                        $cantAccessWithOrderPerDay['accessible_value']
                    )
                );
            }
        } catch (GraphQlSubUserAccessException $e) {
            throw $e;
        } catch (NoSuchEntityException $e) {
            throw new GraphQlSubUserAccessException(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: %1", $e)
            );
            throw new GraphQlSubUserAccessException(
                __(
                    "Sorry we can't let you perform this action!"
                )
            );
        }
        return true;
    }
}
