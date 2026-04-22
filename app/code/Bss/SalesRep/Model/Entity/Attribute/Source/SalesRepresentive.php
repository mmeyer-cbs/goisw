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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model\Entity\Attribute\Source;

use Bss\SalesRep\Model\ResourceModel\User;
use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory;

/**
 * Class SalesRepresentive
 *
 * @package Bss\SalesRep\Model\Entity\Attribute\Source
 */
class SalesRepresentive extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Role is Sales Rep
     */
    const ROLE_IS_SALES_REP = 1;

    /**
     * Sales Rep is none
     */
    const SALES_REP_NONE = 0;

    /**
     * @var Collection
     */
    protected $roleCollection;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var User
     */
    protected $user;

    /**
     * SalesRepresentive constructor.
     * @param Collection $roleCollection
     * @param CollectionFactory $collectionFactory
     * @param User $user
     */
    public function __construct(
        Collection $roleCollection,
        CollectionFactory $collectionFactory,
        \Bss\SalesRep\Model\ResourceModel\User $user
    ) {
        $this->roleCollection = $roleCollection;
        $this->collectionFactory = $collectionFactory;
        $this->user = $user;
    }

    /**
     * Get Sales Rep
     *
     * @return array
     */
    public function getAllOptions()
    {
        $rolesIsSalesRep = $this->roleCollection->addFieldToSelect('*')
            ->addFieldToFilter('is_sales_rep', self::ROLE_IS_SALES_REP)->toOptionArray();
        $userId = [];
        foreach ($rolesIsSalesRep as $role) {
            $parentId = $role['value'];
            $userRoles = $this->collectionFactory->create()
                ->addFieldToSelect('*')->addFieldToFilter('parent_id', $parentId);
            foreach ($userRoles as $user) {
                $userId[] = $user['user_id'];
            }
        }
        $ids = $userId;

        $data = $this->user->joinTableUser();

        $toOptionArray[] = ['value' => self::SALES_REP_NONE, 'label' => ' '];
        foreach ($data as $item) {
            if (in_array($item['user_id'], $ids)) {
                $toOptionArray[] = ['value' => $item['user_id'], 'label' => $item['user_name']];
            }
        }
        return $toOptionArray;
    }
}
