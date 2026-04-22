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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model\ResourceModel\ManageQuote\Grid;

use Bss\QuoteExtension\Model\ModuleCompatible;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 */
class Collection extends \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\Collection implements SearchResultInterface
{
    /**
     * @var ModuleCompatible
     */
    protected $moduleCompatible;

    /**
     * @var array
     */
    protected $condition = [];

    /**
     * Main Table
     *
     * @var string $mainTable
     */
    protected $mainTable;

    /**
     * Resource Model
     *
     * @var string $resourceModel
     */
    protected $resourceModel;

    /**
     * Aggregations
     * @var \Magento\Framework\Api\Search\AggregationInterface
     */
    private $aggregations;

    /**
     * Collection constructor.
     * @param array $condition
     * @param ModuleCompatible $moduleCompatible
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ModuleCompatible $moduleCompatible,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        AdapterInterface $connection = null,
        AbstractDb $resource = null,
        $condition = []
    ) {
        parent::__construct(
            $moduleCompatible,
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource,
            $condition
        );
        $this->condition = $condition;
        $this->moduleCompatible = $moduleCompatible;
        $this->setMainTable($mainTable);
        $this->mainTable = $mainTable;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \Bss\QuoteExtension\Model\ResourceModel\ManageQuote::class
        );
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['customer_grid_flat' => $this->getTable('customer_grid_flat')],
            'main_table.customer_id = customer_grid_flat.entity_id',
            ["IF(customer_grid_flat.`entity_id`, `customer_grid_flat`.`name`, main_table.`customer_name`) as customer_name"]
        );
        $this->addFilterToMap('customer_name', 'customer_name');
        $this->addFilterToMap('customer_id', 'main_table.customer_id');
        $this->addFilterToMap("email", "main_table.email");
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('updated_at', 'main_table.updated_at');
        if ($attributeIdCompanyAccount = $this->moduleCompatible->getAttributeIdCompanyAccount()) {
            $this->getSelect()->joinLeft(
                ['customer_entity_int' => $this->getTable('customer_entity_int')],
                'main_table.customer_id = customer_entity_int.entity_id AND customer_entity_int.attribute_id= ' . $attributeIdCompanyAccount,
                ["bss_is_company_account" => "customer_entity_int.value"]
            );
        }
        if ($this->moduleCompatible->isInstallSalesRep()) {
            $this->addFilterToMap('sales_rep', 'admin_user.user_id');
            $this->getSelect()->joinLeft(
                ['admin_user' => $this->getTable('admin_user')],
                'main_table.user_id = admin_user.user_id',
                ["sales_rep" => "admin_user.user_id"]
            );
        }
    }

    /**
     * Get Aggregations
     *
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set Aggregations
     *
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     *
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws LocalizedException
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }


    /**
     * Create all ids retrieving select with limitation
     *
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Framework\DB\Select
     * @throws LocalizedException
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param array $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
