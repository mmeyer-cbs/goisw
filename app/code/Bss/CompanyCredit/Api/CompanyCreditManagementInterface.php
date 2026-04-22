<?php
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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Api;

/**
 * Company Credit Management
 *
 * @api
 * @since 100.0.0
 */
interface CompanyCreditManagementInterface
{

    /**
     * Get module configs
     *
     * @param int $websiteId
     * @return string[]
     */
    public function getConfig($websiteId = null);

    /**
     * Get Credit by customer id
     *
     * @param int $customerId
     * @return mixed
     */
    public function getCredit($customerId);

    /**
     * Get History Credit by customer id
     *
     * @param int $customerId
     * @return mixed
     */
    public function getCreditHistory($customerId);
}
