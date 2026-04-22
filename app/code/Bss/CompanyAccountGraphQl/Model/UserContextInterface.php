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

namespace Bss\CompanyAccountGraphQl\Model;

interface UserContextInterface extends \Magento\Authorization\Model\UserContextInterface
{
    /**#@+
     * User type
     */
    const USER_TYPE_SUB_USER = 5;
    /**#@-*/

    /**
     * Identify current sub-user ID.
     *
     * @return int|null
     */
    public function getSubUserId(): ?int;
}
