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
namespace Bss\SalesRep\Model\ResourceModel\ManageQuote\Grid;

use Bss\SalesRep\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 *
 * @package Bss\SalesRep\Model\ResourceModel\ManageQuote\Grid
 */
class Collection extends \Bss\SalesRep\Model\ResourceModel\ManageQuote\Collection
{
    /**
     * Sale Rep id null
     */
    const NOT_SALES_REP = -1;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * Collection constructor.
     * @param Attribute $eavAttribute
     * @param Session $authSession
     * @param Data $helper
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param string $identifierName
     * @param string $connectionName
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Attribute $eavAttribute,
        Session $authSession,
        Data $helper,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'quote_extension',
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->authSession = $authSession;
        $this->helper = $helper;
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
     * Join left 2 table  bss_sub_user and customer_entity_int
     * To get Sub User Name
     *
     * @return \Bss\SalesRep\Model\ResourceModel\ManageQuote\Collection|Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        if ($this->helper->isEnableCompanyAccount()) {
            $attributeId = $this->eavAttribute->getIdByCode("customer", "bss_is_company_account");
            $this->addFilterToMap('sub_name', 'bss_sub_user.sub_name');
            $this->getSelect()->joinLeft(
                ['bss_sub_user' => $this->getTable('bss_sub_user')],
                'main_table.sub_user_id = bss_sub_user.sub_id',
                ['sub_name', "sub_email"]
            );
            $this->getSelect()->joinLeft(
                ['customer_entity_int' => $this->getTable('customer_entity_int')],
                'main_table.customer_id = customer_entity_int.entity_id AND customer_entity_int.attribute_id= ' . $attributeId,
                ["bss_is_company_account" => "customer_entity_int.value"]
            );
        }
    }

    /**
     * Quote Collection
     *
     * @return Collection
     */
    protected function _renderFiltersBefore()
    {
        $noSalesRep = self::NOT_SALES_REP;
        $salesRepId = $this->helper->getSalesRepId();
        array_push($salesRepId, $noSalesRep);
        $userId = $this->authSession->getUser()->getId();
        if ($this->helper->checkUserIsSalesRep()) {
            $this->addFieldToSelect('*')->addFieldToFilter('main_table.user_id', $userId);
        } else {
            $this->addFieldToSelect('*')->addFieldToFilter('main_table.user_id', $salesRepId);
        }
        return $this;
    }
}
