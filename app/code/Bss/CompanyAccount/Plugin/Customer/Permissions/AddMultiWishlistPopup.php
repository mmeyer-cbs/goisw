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
namespace Bss\CompanyAccount\Plugin\Customer\Permissions;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class AddMultiWishlistPopup
 *
 * @package Bss\CompanyAccount\Plugin\Customer\Permissions
 */
class AddMultiWishlistPopup
{
    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * MultiWishListAddFromCart constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        PermissionsChecker $permissionsChecker,
        JsonFactory $jsonFactory
    ) {
        $this->permissionsChecker = $permissionsChecker;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Check if logged in is sub-user and have permission to access this action
     *
     * @param \Bss\MultiWishlist\Controller\Index\Popup $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Bss\MultiWishlist\Controller\Index\Popup $subject,
        callable $proceed
    ) {
        $checkValue = $this->permissionsChecker->check(Permissions::ADD_VIEW_ACCOUNT_WISHLIST);
        if ($checkValue) {
            $result = $this->jsonFactory->create();
            $this->permissionsChecker->getMessageManager()->getMessages(true);
            $result->setData(
                [
                'cant_access' => true,
                'error_message' => __('You have no permission to this action.')
                ]
            );
            return $result;
        }
        return $proceed();
    }
}
