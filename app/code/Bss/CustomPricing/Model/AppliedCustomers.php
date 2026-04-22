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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model;

use Bss\CustomPricing\Model\ResourceModel\AppliedCustomers as ResourceAppliedCustomers;
use Bss\CustomPricing\Model\ResourceModel\PriceRule as PriceRuleAlias;
use Magento\Framework\Model\AbstractModel;
use Bss\CustomPricing\Model\ResourceModel\AppliedCustomers as ResourceModel;
use Bss\CustomPricing\Api\Data\AppliedCustomersInterface;

/**
 * The price rule model class
 */
class AppliedCustomers extends AbstractModel implements AppliedCustomersInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'bss_applied_customers';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getAppliedCustomers() in this case
     *
     * @var string
     */
    protected $_eventObject = 'applied_customers';

    /**
     * Init Price Rule model
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel::class);
        $this->setIdFieldName(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerFirstName()
    {
        return $this->_getData(self::CUSTOMER_FIRST_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerFirstName($val)
    {
        return $this->setData(self::CUSTOMER_FIRST_NAME, $val);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerLastName()
    {
        return $this->_getData(self::CUSTOMER_LAST_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerLastName($val)
    {
        return $this->setData(self::CUSTOMER_LAST_NAME, $val);
    }

    /**
     * @inheritDoc
     */
    public function getRuleId()
    {
        return $this->_getData(self::RULE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRuleId($val)
    {
        return $this->setData(self::RULE_ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($val)
    {
        return $this->setData(self::CUSTOMER_ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getAppliedRule()
    {
        return $this->_getData(self::APPLIED_RULE);
    }

    /**
     * @inheritDoc
     */
    public function setAppliedRule($val)
    {
        return $this->setData(self::APPLIED_RULE, $val);
    }

    /**
     * Get customer rule by customer id
     *
     * @param int $customerId
     * @return false|string
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCustomerRule($customerId, $websiteId)
    {
        $data = [];
        $connection = $this->getResource()->getConnection();
        $resource = $this->getResource();
        $select = $connection->select()->from(
            ['e' => $resource->getTable(ResourceAppliedCustomers::TABLE)]
        )->join(
            ['rules' => $resource->getTable(PriceRuleAlias::TABLE)],
            'rules.id = e.rule_id'
        )->where(
            'e.customer_id = ?',
            $customerId
        )->where(
            'rules.status = ?',
            true
        )->where(
            'e.applied_rule = ?',
            true
        )->where(
            'rules.website_id = ?',
            $websiteId
        )->reset(
            \Magento\Framework\DB\Select::COLUMNS
        )->columns(['e.rule_id'])->order(['e.rule_id']);
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            $data[] = $row['rule_id'];
        }
        if (!empty($data)) {
            return (implode("-", $data));
        }
        return false;
    }
}
