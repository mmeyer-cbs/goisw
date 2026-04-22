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
namespace Bss\CompanyCredit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Bss\CompanyCredit\Model\CreditFactory;

class Credit extends AbstractDb
{
    /**
     * @var \Bss\CompanyCredit\Model\CreditFactory
     */
    private $creditFactory;

    /**
     * Construct.
     *
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
        $this->_init('bss_companycredit_credit', 'id');
    }

    /**
     * Load companycredit by customer
     *
     * @param \Bss\CompanyCredit\Model\Credit $credit
     * @param int $customerId
     * @return \Bss\CompanyCredit\Model\Credit
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomer($credit, $customerId)
    {
        $connection = $this->getConnection();
        $bind = ['customer_id' => $customerId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['id']
        )->where(
            'customer_id = :customer_id'
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
