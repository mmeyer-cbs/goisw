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

namespace Bss\CompanyAccount\Observer;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;

/**
 * Class AfterGenerateLayoutBlocks
 *
 * @package Bss\CompanyAccount\Observer
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AfterGenerateLayoutBlocks implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout = null;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * AfterGenerateLayoutBlocks constructor.
     *
     * @param Data $helper
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubRoleRepositoryInterface $roleRepository
     * @param Session $customerSession
     */
    public function __construct(
        Data                       $helper,
        SubUserRepositoryInterface $subUserRepository,
        SubRoleRepositoryInterface $roleRepository,
        Session                    $customerSession
    ) {
        $this->helper = $helper;
        $this->storeManager = $this->helper->getStoreManager();
        $this->roleRepository = $roleRepository;
        $this->customerSession = $customerSession;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Check and remove can't access blocks
     *
     * @param \Bss\CompanyAccount\Model\SubUser $subUser
     * @param array $dataArray
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterface $assignedRole
     *
     * @return void
     */
    protected function checkAndRemoveCantAccessBlock($subUser, $dataArray, $assignedRole)
    {
        foreach ($dataArray as $item) {
            $currentHandles = $this->layout->getUpdate()->getHandles();
            if (!$subUser->canAccess($item['key'], $assignedRole) &&
                $item['key'] == Permissions::VIEW_ACCOUNT_DASHBOARD
            ) {
                if (in_array('customer_account_index', $currentHandles)) {
                    $this->layout->unsetElement('customer_account_dashboard_info');
                    $this->layout->unsetElement('customer_account_dashboard_info1');
                    $this->layout->unsetElement('customer_account_dashboard_top');
                    $this->layout->unsetElement('customer_account_dashboard_address');
                    $this->layout->unsetElement('page.main.title');
                }
            } elseif (!$subUser->canAccess($item['key'], $assignedRole)) {
                foreach ($item['ele'] as $ele) {
                    $this->layout->unsetElement($ele);
                }
            }
            if (!$subUser->canAccess(Permissions::ADD_VIEW_ADDRESS_BOOK, $assignedRole)) {
                $this->layout->unsetElement('customer_account_dashboard_address');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $observer->getLayout();
        $this->layout = $layout;
        /** @var \Bss\CompanyAccount\Model\SubUser $subUser */
        $subUser = $this->customerSession->getSubUser();
        if ($this->helper->isEnable($this->storeManager->getWebsite()->getId()) &&
            $subUser
        ) {
            $subUser = $this->subUserRepository->getById($subUser->getSubId());
            /** @var \Bss\CompanyAccount\Api\Data\SubRoleInterface $assignedRole */
            $assignedRole = $this->roleRepository->getById($subUser->getRoleId());
            $dataArray = [
                [
                    'key' => Permissions::VIEW_ACCOUNT_DASHBOARD,
                    'ele' => ['page.main.title']
                ],
                [
                    'key' => Permissions::VIEW_DOWNLOADABLE_PRODUCT,
                    'ele' => ['customer-account-navigation-downloadable-products-link']
                ],
                [
                    'key' => Permissions::MANAGE_SUB_USER_AND_ROLES,
                    'ele' => [
                        'customer-account-navigation-manage-sub-user',
                        'customer-account-navigation-manage-sub-role'
                    ]
                ],
                [
                    'key' => Permissions::VIEW_REPORT,
                    'ele' => [
                        'customer-account-navigation-sub-user-report'
                    ]
                ],
                [
                    'key' => Permissions::ADD_VIEW_ACCOUNT_WISHLIST,
                    'ele' => ['customer-account-navigation-wish-list-link']
                ],
                [
                    'key' => Permissions::ADD_VIEW_ADDRESS_BOOK,
                    'ele' => ['customer-account-navigation-address-link']
                ],
                [
                    'key' => Permissions::VIEW_STORED_PAYMENT_METHOD,
                    'ele' => ['customer-account-navigation-my-credit-cards-link']
                ]
            ];

            if (!$subUser->canAccess(Permissions::ADMIN, $assignedRole)) {
                $this->checkAndRemoveCantAccessBlock($subUser, $dataArray, $assignedRole);
            }
        }
        return $this;
    }
}
