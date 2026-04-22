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
 * @category  BSS
 * @package   Bss_SalesRep
 * @author    Extension Team
 * @copyright Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;

/**
 * Class CreatedByAdminOrder
 *
 * @package Bss\SalesRep\Model\ResourceModel
 */
class OrderCreatedByAdmin extends AbstractDb
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * OrderCreatedByAdmin constructor.
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $connectionName
     */
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        $this->logger = $logger;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('order_created_by_admin', 'id');
    }

    /**
     * Save data table order_created_by_admin from table sales_order
     *
     * @param array $data
     * @throws LocalizedException
     */
    public function insertMultiple($data)
    {
        try {
            $connection = $this->getConnection();
            $table = $this->getMainTable();
            $connection->insertMultiple($table, $data);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
