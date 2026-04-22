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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model;

use Magento\Framework\App\ResourceConnection;

class ConnectDB
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Is address of user
     *
     * @param int $addressId
     * @param int $userId
     * @return bool
     */
    public function isAddressOfUser($addressId, $userId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['sales_order_address' => $this->resource->getTableName('sales_order_address')],
                ''
            )
            ->join(
                ['bss_sales_rep_order' => $this->resource->getTableName('bss_sales_rep_order')],
                'bss_sales_rep_order.order_id = sales_order_address.parent_id',
                ['user_id' => 'bss_sales_rep_order.user_id']
            )
            ->where("sales_order_address.entity_id = ?", $addressId);
        $userIdQuery = $connection->fetchOne($select);
        if ($userId == $userIdQuery) {
            return true;
        }
        return false;
    }

}
