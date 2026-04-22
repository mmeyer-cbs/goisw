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
namespace Bss\CompanyAccount\Plugin\Multishipping;

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;

/**
 * Class CreateAddressPermissionCheck
 *
 * @package Bss\CompanyAccount\Plugin\Multishipping
 */
class CreateAddressPermissionCheck
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PermissionsChecker
     */
    private $checker;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * CreateAddressPermissionCheck constructor.
     *
     * @param Data $helper
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param PermissionsChecker $checker
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        PermissionsChecker $checker
    ) {
        $this->helper = $helper;
        $this->checker = $checker;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Check and redirect to homepage if sub-user have no create address permission
     *
     * @param Object $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute($subject, callable $proceed)
    {
        if ($this->helper->isEnable() &&
            $this->helper->getCustomerSession()->getSubUser()
        ) {
            if ($this->checker->isDenied(Permissions::ADD_VIEW_ADDRESS_BOOK)) {
                $this->checker->getMessageManager()->addErrorMessage(__('You have no permission to to this action.'));
                return $this->resultRedirectFactory->create()
                    ->setPath('');
            }
        }
        return $proceed();
    }
}
