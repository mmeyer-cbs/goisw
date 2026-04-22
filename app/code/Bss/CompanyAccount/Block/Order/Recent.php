<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\Order;

use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Recent
 */
class Recent extends \Magento\Sales\Block\Order\Recent
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    private $subUserOrderRepository;

    /**
     * @var PermissionsChecker
     */
    private $checker;

    /**
     * Recent constructor.
     *
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     * @param Data $helper
     * @param PermissionsChecker $checker
     * @param SubUserOrderRepositoryInterface $subUserRepository
     * @param array $data
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig,
        Data $helper,
        PermissionsChecker $checker,
        SubUserOrderRepositoryInterface $subUserRepository,
        array $data = [],
        StoreManagerInterface $storeManager = null
    ) {
        $this->helper = $helper;
        $this->storeManager = $this->helper->getStoreManager();
        $this->customerSession = $this->helper->getCustomerSession();
        $this->subUserOrderRepository = $subUserRepository;
        $this->checker = $checker;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data, $storeManager);
    }

    /**
     * Recent order by sub-user construct
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->customerSession->getSubUser()) {
            if ($this->checker->isDenied(Permissions::VIEW_ALL_ORDER)) {
                $this->getRecentOrderBySubUser();
            }
        }
    }

    /**
     * Get recent order if logged in is sub-user
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRecentOrderBySubUser()
    {
        $orders = $this->_orderCollectionFactory->create()->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $this->getAvailableOrders()])
            ->addAttributeToFilter('customer_id', $this->_customerSession->getCustomerId())
            ->addAttributeToFilter('store_id', $this->storeManager->getStore()->getId())
            ->addAttributeToFilter('status', ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()])
            ->addAttributeToSort('created_at', 'desc')
            ->setPageSize(self::ORDER_LIMIT)
            ->load();
        $this->setOrders($orders);
    }

    /**
     * Get array orders by subUser
     *
     * @return array
     */
    private function getAvailableOrders()
    {
        return $this->subUserOrderRepository->getBySubUser(
            $this->customerSession->getSubUser()->getSubId()
        );
    }
}
