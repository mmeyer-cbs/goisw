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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Api;

use Bss\CompanyCredit\Api\Data\CreditInterface;

/**
 * @api
 */
interface CreditRepositoryInterface
{
    /**
     * Retrieve customer.
     *
     * @param null|int $customerId
     * @return \Bss\CompanyCredit\Api\CreditRepositoryInterface
     */
    public function get($customerId = null);

    /**
     * Save credit
     *
     * @param CreditInterface $creditInterface
     * @return mixed
     */
    public function save(CreditInterface $creditInterface);
}
