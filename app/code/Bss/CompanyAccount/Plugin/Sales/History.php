<?php
declare(strict_types = 1);

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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\Sales;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\Config\Source\Permissions;

/**
 * Class History
 *
 * @package Bss\CompanyAccount\Plugin\Sales
 */
class History
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollection;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    private $subUserOrderRepository;

    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * History constructor.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param SubRoleRepositoryInterface $roleRepository
     * @param SubUserOrderRepositoryInterface $subUserOrderRepository
     * @param Data $helper
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Model\Order\Config $orderConfig,
        SubRoleRepositoryInterface $roleRepository,
        SubUserOrderRepositoryInterface $subUserOrderRepository,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->orderCollection = $orderCollection;
        $this->orderConfig = $orderConfig;
        $this->subUserOrderRepository = $subUserOrderRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * If module is enable and logged in is sub-user
     *
     * @param \Magento\Sales\Block\Order\History $subject
     * @param callable $proceed
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetOrders(\Magento\Sales\Block\Order\History $subject, callable $proceed)
    {
        /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
        $subUser = $this->customerSession->getSubUser();
        if ($this->helper->isEnable() &&
            $subUser
        ) {
            $relatedRole = $this->roleRepository->getById($subUser->getRoleId());
            if ($subUser->canAccess(Permissions::ADMIN, $relatedRole) ||
                $subUser->canAccess(Permissions::VIEW_ALL_ORDER, $relatedRole)
            ) {
                return $proceed();
            }
            if (!($customerId = $this->customerSession->getCustomerId())) {
                return false;
            }
            if (!$this->orders) {
                $this->orders = $this->orderCollection->create($customerId)->addFieldToSelect('*')
                    ->addFieldToFilter(
                    'status',
                    [
                        'in' => $this->orderConfig->getVisibleOnFrontStatuses()
                    ]
                    )->addFieldToFilter(
                    'entity_id',
                    [
                        'in' => $this->getAvailableOrders($subUser->getSubId())
                    ]
                    )->setOrder(
                    'created_at',
                    'desc'
                );
            }
            return $this->orders;
        }
        return $proceed();
    }

    /**
     * Get array orders by subUser
     *
     * @param int $subUserId
     * @return array
     */
    private function getAvailableOrders($subUserId)
    {
        return $this->subUserOrderRepository->getBySubUser($subUserId);
    }
}
