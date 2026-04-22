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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model\ResourceModel\ManageQuote;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 *
 * @package Bss\SalesRep\Model\ResourceModel\ManageQuote
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Collection constructor.
     * Not move because declare maintable
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param string $identifierName
     * @param string $connectionName
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'quote_extension',
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName,
            $connectionName
        );
    }

    /**
     * ManageQuote
     *
     * @return $this|Collection|void
     */
    protected function _initSelect()
    {
        $tableDescription = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($tableDescription as $columnInfo) {
            $this->addFilterToMap($columnInfo['COLUMN_NAME'], 'main_table.' . $columnInfo['COLUMN_NAME']);
        }
        $this->addFilterToMap('sales_rep', 'admin_user.username');
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['admin_user' => $this->getTable('admin_user')],
            'main_table.user_id = admin_user.user_id',
            ['sales_rep' => 'admin_user.username']
        );
        $this->getSelect()->joinLeft(
            ['quoteTable' => $this->getTable('quote')],
            'main_table.target_quote = quoteTable.entity_id',
            ['grand_total', 'quote_currency_code']
        );
        $this->getSelect()->joinLeft(
            ['customer_grid_flat' => $this->getTable('customer_grid_flat')],
            "main_table.customer_id = customer_grid_flat.entity_id",
            ["customer_name" => "customer_grid_flat.name"]
        );
        $this->addFilterToMap('customer_name', 'customer_grid_flat.name');
        return $this;
    }
}
