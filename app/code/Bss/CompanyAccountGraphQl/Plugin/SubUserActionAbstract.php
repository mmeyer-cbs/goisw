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

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccountGraphQl\Exception\GraphQlSubUserAccessException;

/**
 * Check permission of current sub-user before perform action
 */
abstract class SubUserActionAbstract
{
    const PERMISSION = 0;
    const ADMIN = Permissions::ADMIN;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * SubUserActionAbstract constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(
        PermissionsChecker $permissionsChecker
    ) {
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Is sub-user is allowed to perform action
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException|GraphQlSubUserAccessException
     */
    protected function isAllowed($context)
    {
        if ($context->getExtensionAttributes()->getIsSubUser()) {
            $subId = $context->getExtensionAttributes()->getSubUserId();

            $can = $this->canAccess($subId, static::ADMIN) || $this->canAccess($subId, static::PERMISSION) ;

            if (!$can) {
                throw new GraphQlSubUserAccessException(
                    __("Sorry we can't let you perform this action!")
                );
            }
        }
    }

    /**
     * Check access
     *
     * @param int $subId
     * @param int|string $permission
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function canAccess(int $subId, $permission): bool
    {
        return $this->permissionsChecker->canSubUserAccess($subId, $permission . "");
    }
}
