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
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Class OrderView
 *
 * @package Bss\CompanyAccount\Plugin\Sales
 */
class OrderView
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    private $subUserOrderRepository;

    /**
     * OrderView constructor.
     *
     * @param Data $helper
     * @param SubUserOrderRepositoryInterface $subUserOrderRepository
     * @param RedirectFactory $redirectFactory
     * @param SubRoleRepositoryInterface $roleRepository
     */
    public function __construct(
        Data $helper,
        SubUserOrderRepositoryInterface $subUserOrderRepository,
        RedirectFactory $redirectFactory,
        SubRoleRepositoryInterface $roleRepository
    ) {
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->roleRepository = $roleRepository;
        $this->redirectFactory = $redirectFactory;
        $this->subUserOrderRepository = $subUserOrderRepository;
    }

    /**
     * Redirect to home page if sub-user access to no permission page
     *
     * @param \Magento\Sales\Controller\AbstractController\View $subject
     * @param callable $proceed
     * @return bool|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Sales\Controller\AbstractController\View $subject, callable $proceed)
    {
        $subUser = $this->customerSession->getSubUser();
        if ($this->helper->isEnable() && $subUser) {
            $relatedRole = $this->roleRepository->getById($subUser->getRoleId());
            $orders = $this->subUserOrderRepository->getBySubUser($subUser->getSubId());
            if ($subUser->canAccess(Permissions::ADMIN, $relatedRole) ||
                $subUser->canAccess(Permissions::VIEW_ALL_ORDER, $relatedRole) ||
                in_array($subject->getRequest()->getParam('order_id'), $orders)
            ) {
                return $proceed();
            }
            $this->helper->getMessageManager()->addErrorMessage(__('You have no permission to access that page.'));
            return $this->redirectFactory->create()
                ->setPath('');
        }
        return $proceed();
    }
}
