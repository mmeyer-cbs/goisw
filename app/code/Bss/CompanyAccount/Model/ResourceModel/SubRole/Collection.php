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
namespace Bss\CompanyAccount\Model\ResourceModel\SubRole;

/**
 * Class Collection
 *
 * @package Bss\CompanyAccount\Model\ResourceModel\SubRole
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /** @var string */
    protected $_idFieldName = 'role_id';

    /**
     * Define resource model.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Bss\CompanyAccount\Model\SubRole::class,
            \Bss\CompanyAccount\Model\ResourceModel\SubRole::class
        );
    }
}
