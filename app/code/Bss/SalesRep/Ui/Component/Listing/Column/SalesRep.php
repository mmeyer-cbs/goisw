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
namespace Bss\SalesRep\Ui\Component\Listing\Column;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Model\ResourceModel\SalesRepOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class SalesRep
 *
 * @package Bss\SalesRep\Ui\Component\Listing\Column
 */
class SalesRep extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteria;

    /**
     * @var SalesRepOrder
     */
    protected $orderFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * SalesRep constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $criteria
     * @param SalesRepOrder $orderFactory
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        SalesRepOrder $orderFactory,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Set Sales Rep in DataSource
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($this->helper->isEnable()) {
            if (isset($dataSource['data']['items'])) {
                foreach ($dataSource['data']['items'] as & $item) {
                    $order_id = $item["entity_id"];

                    $username = '';
                    $collection = $this->orderFactory->joinTableOrder();
                    foreach ($collection as $value) {
                        if ($value['order_id'] == $order_id) {
                            $username = $value['user_name'];
                        }
                    }
                    $item[$this->getData('name')] = $username;
                }
            }
        }
        return $dataSource;
    }
}
