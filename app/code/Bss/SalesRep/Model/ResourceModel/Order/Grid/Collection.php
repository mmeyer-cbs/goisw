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
namespace Bss\SalesRep\Model\ResourceModel\Order\Grid;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Model\Entity\Attribute\Source\SalesRepresentive;
use Bss\SalesRep\Model\ResourceModel\SalesRepOrder;
use Magento\Backend\Model\Auth\Session;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Zend_Db_Statement_Exception;

/**
 * Class Collection
 *
 * @package Bss\SalesRep\Model\ResourceModel\Order\Grid
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Collection extends \Bss\SalesRep\Model\ResourceModel\Grid\Collection
{
    /**
     * @var
     */
    protected $eavAttribute;
    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var SalesRepOrder
     */
    protected $orderFactory;

    /**
     * @var SalesRepresentive
     */
    protected $salesRepresentive;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Collection constructor.
     *
     * @param Attribute $eavAttribute
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param Session $authSession
     * @param SalesRepOrder $orderFactory
     * @param SalesRepresentive $salesRepresentive
     * @param Data $helper
     */
    public function __construct(
        Attribute $eavAttribute,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        Session $authSession,
        SalesRepOrder $orderFactory,
        SalesRepresentive $salesRepresentive,
        Data $helper
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->helper = $helper;
        $this->_authSession = $authSession;
        $this->orderFactory = $orderFactory;
        $this->salesRepresentive = $salesRepresentive;
        parent::__construct($helper, $entityFactory, $logger, $fetchStrategy, $eventManager);
    }

    /**
     * Order Collection
     *
     * @throws Zend_Db_Statement_Exception
     */
    protected function _renderFiltersBefore()
    {
        $userId = $this->_authSession->getUser()->getId();
        $userOrder = $this->orderFactory->joinTableOrder()->fetchAll();
        $allId = [0];
        if ($this->helper->checkUserIsSalesRep()) {
            $orderId = [];
            foreach ($userOrder as $item) {
                if ($item['user_id'] == $userId) {
                    $orderId[] = $item['order_id'];
                }
            }
            if (empty($orderId)) {
                $this->addFieldToSelect('*')->addFieldToFilter('entity_id', 0);
            } else {
                $this->addFieldToSelect('*')->addFieldToFilter('entity_id', $orderId);
            }
        }
        foreach ($userOrder as $item) {
            $allId[] = $item['order_id'];
        }
        $this->addFieldToSelect('*')->addFieldToFilter('entity_id', $allId);

        parent::_renderFiltersBefore();
    }

    /**
     * @return Collection|\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult|\Magento\Sales\Model\ResourceModel\Order\Grid\Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['order_created_by_admin' => $this->getTable('order_created_by_admin')],
            'main_table.entity_id = order_created_by_admin.order_id ',
            ["created_by_admin" => " order_created_by_admin.created_by_admin"]
        );
        $attributeId = $this->eavAttribute->getIdByCode("customer", "bss_is_company_account");
        if ($attributeId) {
            $this->getSelect()->joinLeft(
                ['customer_entity_int' => $this->getTable('customer_entity_int')],
                'main_table.customer_id = customer_entity_int.entity_id AND customer_entity_int.attribute_id= ' . $attributeId,
                ["bss_is_company_account" => "customer_entity_int.value"]
            );
        }
        $this->distinct(true);
    }
}
