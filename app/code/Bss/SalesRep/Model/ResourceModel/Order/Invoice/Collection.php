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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model\ResourceModel\Order\Invoice;

use Bss\SalesRep\Helper\Data;
use Magento\Framework\Authorization;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 *
 * @package Bss\SalesRep\Model\ResourceModel\Order\Invoice
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Authorization
     */
    protected $authorization;

    /**
     * @param Authorization $authorization
     * @param Data $helper
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param null $resourceModel
     * @param null $identifierName
     * @param null $connectionName
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Authorization $authorization,
        Data $helper,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = "sales_invoice_grid",
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    ) {
        $this->helper = $helper;
        $this->authorization = $authorization;
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
     * Filter invoice assign sales rep
     *
     * @return Collection|void
     */
    protected function _initSelect()
    {
        $customerAllowed = $this->authorization->isAllowed('Magento_Sales::sales');
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep() && !$customerAllowed) {
            $orderIds = $this->helper->arrayOrderSalesRepId();
            if (!empty($orderIds)) {
                $this->addFieldToSelect('*')->addFieldToFilter('order_id', $orderIds);
            }
        }
        return parent::_initSelect();
    }
}
