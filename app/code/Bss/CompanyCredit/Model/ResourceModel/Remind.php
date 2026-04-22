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

use Bss\CompanyCredit\Model\RemindFactory as RemindFactoryAlias;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Remind extends AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var RemindFactoryAlias
     */
    private $remindFactory;

    /**
     * Construct.
     *
     * @param Context $context
     * @param DateTime $date
     * @param RemindFactoryAlias $remindFactory
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $date,
        RemindFactoryAlias $remindFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->date = $date;
        $this->remindFactory = $remindFactory;
    }

    /**
     * Construct.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('bss_c_c_remind_credit', 'id');
    }

    /**
     * Load remind by id history
     *
     * @param \Bss\CompanyCredit\Model\Remind $remind
     * @param int $idHistory
     * @return \Bss\CompanyCredit\Model\Remind|Remind|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByIdHistory($remind, $idHistory)
    {
        $connection = $this->getConnection();
        $bind = ['id_history' => $idHistory];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['id']
        )->where(
            'id_history = :id_history'
        );

        $remindId = $connection->fetchOne($select, $bind);
        if ($remindId) {
            $remind = $this->remindFactory->create()->load($remindId);
        } else {
            $remind->setData([]);
        }

        return $remind;
    }

    /**
     * Save multiple data in table remind.
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function insertMultiple($data)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $connection->insertMultiple($table, $data);
    }
}
