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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Api;

use Bss\CompanyCredit\Api\Data\RemindInterface;

/**
 * @api
 */
interface RemindRepositoryInterface
{
    /**
     * Get remind by ID.
     *
     * @param int $id
     * @return \Bss\CompanyCredit\Api\RemindRepositoryInterface
     */
    public function getByIdHistory($id);

    /**
     * Save remind
     *
     * @param RemindInterface|mixed $remindInterface
     * @return mixed
     */
    public function save($remindInterface);

    /**
     * Save remind
     *
     * @param array $data
     * @return mixed
     */
    public function insertMultiple($data);

    /**
     * Delete remind
     *
     * @param RemindInterface|mixed $remindInterface
     * @return mixed
     */
    public function delete($remindInterface);
}
