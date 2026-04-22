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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\B2bRegistration\Helper\Data;
use Psr\Log\LoggerInterface;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;
use Bss\B2bRegistration\Model\CustomerStatusFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

/**
 * Class UpgradeStatusObserver
 *
 * @package Bss\B2bRegistration\Observer
 */
class UpgradeStatusObserver implements ObserverInterface
{
    /**
     * @var \Bss\B2bRegistration\Model\CustomerStatusFactory
     */
    protected $customerStatusFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * UpgradeStatusObserver constructor.
     * @param CustomerStatusFactory $customerStatusFactory
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     */
    public function __construct(
        CustomerStatusFactory $customerStatusFactory,
        Data $helper,
        LoggerInterface $logger,
        IndexerRegistry $indexerRegistry,
        Attribute $eavAttribute
    ) {
        $this->customerStatusFactory = $customerStatusFactory;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * Set Normal status to normal account
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            try {
                $b2bAttributeId = $this->eavAttribute->getIdByCode('customer', 'b2b_activasion_status');
                $customer = $observer->getCustomer();
                $data['attribute_id'] = $b2bAttributeId;
                $data['entity_id'] = $customer->getId();
                $data['value'] = CustomerAttribute::NORMAL_ACCOUNT;
                $this->customerStatusFactory->create()->setData($data)->save();
                $indexer = $this->indexerRegistry->get('customer_grid');
                $indexer->reindexRow($customer->getId());
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }
}
