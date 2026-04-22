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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Bss\CustomPricing\Model\AppliedCustomers as AppliedCustomersModel;

/**
 * Applied customer resource class
 */
class AppliedCustomers extends AbstractDb
{
    const TABLE = 'bss_applied_customers';
    const PRIMARY_KEY = 'id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, self::PRIMARY_KEY);
    }

    /**
     * Load applied customer by rule and customer id
     *
     * @param string|int $ruleId
     * @param string|int $customerId
     * @param AppliedCustomersModel $customer
     * @return $this|false
     */
    public function loadBy($ruleId, $customerId, $customer)
    {
        try {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getMainTable(), "*");
            $select->where(sprintf(
                "rule_id = %s AND customer_id = %s",
                $ruleId,
                $customerId
            ));
            $data = $connection->fetchRow($select);

            if ($data) {
                $customer->setData($data);
            }

            $this->_afterLoad($customer);
            $customer->afterLoad();
            $customer->setOrigData();
            $customer->setHasDataChanges(false);

            return $this;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * The customer id has applied on rule
     *
     * @param int $ruleId
     * @param int $customerId
     * @return bool|string
     */
    public function hasCustomer($ruleId, $customerId)
    {
        try {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getMainTable());
            $select->where(sprintf("rule_id = %s AND customer_id = %s", $ruleId, $customerId))
                ->limit(1);
            return $connection->fetchOne($select);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete old customers
     *
     * @param int $ruleId
     * @param string $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return false|void
     */
    public function removeOldCustomers($ruleId, $ids)
    {
        try {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getMainTable());
            $select->where(sprintf("rule_id = %s AND id IN (%s)", $ruleId, $ids));
            $query = $select->deleteFromSelect($this->getMainTable());
            $connection->query($query);
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }
}
