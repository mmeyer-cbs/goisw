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
namespace Bss\SalesRep\Observer\Model;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Model\SalesRepFactory;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory;
use Magento\Authorization\Model\RoleFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\PageCache\Model\Config;

/**
 * Class User
 *
 * @package Bss\SalesRep\Observer\Model
 */
class User implements ObserverInterface
{
    /**
     * @var SalesRepFactory
     */
    protected $salesRepFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RoleFactory
     */
    protected $roleFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * User constructor.
     * @param SalesRepFactory $salesRepFactory
     * @param RoleFactory $roleFactory
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param Data $helper
     * @param ManagerInterface $messageManager
     * @param Config $config
     * @param TypeListInterface $typeList
     */
    public function __construct(
        SalesRepFactory $salesRepFactory,
        RoleFactory $roleFactory,
        CollectionFactory $collectionFactory,
        Registry $registry,
        Data $helper,
        ManagerInterface $messageManager,
        Config $config,
        TypeListInterface $typeList
    ) {
        $this->salesRepFactory = $salesRepFactory;
        $this->collectionFactory = $collectionFactory;
        $this->roleFactory = $roleFactory;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->typeList = $typeList;
    }

    /**
     * Set data to bss_sales_rep table
     *
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helper->isEnable()) {
            $data = $observer->getObject();
            $userId = $data['user_id'];
            /**
             * Create table bss_sales_rep
             */
            $user = $this->salesRepFactory->create();
            $user->load($userId, 'user_id');
            $user->setUserId($data['user_id']);
            $user->setInformation($data['content']);
            try {
                $user->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            if ($this->config->isEnabled()) {
                $this->typeList->invalidate(
                    \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
                );
            }
            return;
        }
    }
}
