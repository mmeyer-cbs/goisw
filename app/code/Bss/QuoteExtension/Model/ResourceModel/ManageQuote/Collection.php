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
namespace Bss\QuoteExtension\Model\ResourceModel\ManageQuote;

use Bss\QuoteExtension\Model\ModuleCompatible;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
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
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Collection constructor.
     * @param $condition
     * @param ModuleCompatible $moduleCompatible
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        ModuleCompatible $moduleCompatible,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null,
        $condition = []
    ) {
        $this->moduleCompatible = $moduleCompatible;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->condition = $condition;
    }

    /**
     * Define stock collection
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(
            \Bss\QuoteExtension\Model\ManageQuote::class,
            ManageQuote::class
        );
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['quoteTable' => $this->getTable('quote')],
            'main_table.target_quote = quoteTable.entity_id',
            ['grand_total', 'quote_currency_code']
        );

        if (isset($this->condition["join_left_admin_user"])) {
            $this->getSelect()
                ->joinLeft(
                    ['admin_user' => $this->getTable('admin_user')],
                    'main_table.user_id = admin_user.user_id',
                    ['sales_rep' => 'admin_user.username']
                );
        }

        if ($this->moduleCompatible->isEnableCompanyAccount()) {
            $this->addFilterToMap('sub_name', 'bss_sub_user.sub_name');
            $this->addFilterToMap('quote_id', 'main_table.quote_id');
            $this->getSelect()->joinLeft(
                ['bss_sub_user' => $this->getTable('bss_sub_user')],
                'main_table.sub_user_id = bss_sub_user.sub_id',
                ['sub_name', "sub_email"]
            );
            $this->getSelect()->joinLeft(
                ['bss_sub_role' => $this->getTable('bss_sub_role')],
                'bss_sub_user.role_id = bss_sub_role.role_id',
                ["role_name" => "bss_sub_role.role_name"]
            )->distinct(true);
        }
    }
}
