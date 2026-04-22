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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Bss\StoreCredit\Model\CreditFactory;

/**
 * Class Credit
 * @package Bss\StoreCredit\Model\ResourceModel
 */
class Credit extends AbstractDb
{
    /**
     * @var \Bss\StoreCredit\Model\CreditFactory
     */
    private $creditFactory;

    /**
     * @param Context $context
     * @param CreditFactory $creditFactory
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        CreditFactory $creditFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->creditFactory = $creditFactory;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('bss_storecredit_balance', 'balance_id');
    }

    /**
     * Load storecredit by customer
     *
     * @param \Bss\StoreCredit\Model\Credit $credit
     * @param int $customerId
     * @return \Bss\StoreCredit\Model\Credit
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomer($credit, $customerId)
    {
        $connection = $this->getConnection();
        $bind = ['customer_id' => $customerId, 'website_id' => $credit->getWebsiteId()];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['balance_id']
        )->where(
            'customer_id = :customer_id'
        )->where(
            'website_id = :website_id'
        );

        $creditId = $connection->fetchOne($select, $bind);
        if ($creditId) {
            $credit = $this->creditFactory->create()->load($creditId);
        } else {
            $credit->setData([]);
        }
        return $credit;
    }
}
