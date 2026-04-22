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
namespace Bss\CompanyAccount\Model\ResourceModel\SubUser;

/**
 * Class Collection
 *
 * @package Bss\CompanyAccount\Model\ResourceModel\SubUser
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Relation fields
     *
     * @var array
     */
    protected $with = [];

    /**
     * @var string
     */
    protected $_idFieldName = 'sub_id';

    /**
     * Define resource model.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Bss\CompanyAccount\Model\SubUser::class,
            \Bss\CompanyAccount\Model\ResourceModel\SubUser::class
        );
    }

    /**
     * Load role detail for sub-user
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('customer_id', 'main_table.customer_id');
        $this->addFilterToMap('role_id', 'main_table.role_id');
        $this->addFilterToMap('role_name', 'detail_role.role_name');
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['subuser_role' => $this->getTable('bss_sub_role')],
            'main_table.role_id = subuser_role.role_id',
            []
        )
            ->joinLeft(
                ['detail_role' => $this->getTable('bss_sub_role')],
                'subuser_role.role_id = detail_role.role_id',
                ['role_name']
            );
    }

    /**
     * Eager loading relation field
     *
     * @param mixed $fields
     * @throws \Bss\CompanyAccount\Exception\RelationMethodNotFoundException
     */
    public function with($fields): Collection
    {
        /** @var \Bss\CompanyAccount\Model\SubUser $item */
        foreach ($this->getItems() as $item) {
            $item->with($fields);
        }

        return $this;
    }
}
