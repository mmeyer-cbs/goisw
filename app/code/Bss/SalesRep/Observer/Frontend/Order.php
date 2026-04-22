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
namespace Bss\SalesRep\Observer\Frontend;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Helper\OrderCreatedByAdmin;
use Bss\SalesRep\Model\SalesRepOrderRepository;
use Exception;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Order
 *
 * @package Bss\SalesRep\Observer\Frontend
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Order implements ObserverInterface
{
    /**
     * @var OrderCreatedByAdmin
     */
    protected $orderCreatedByAdmin;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var State
     */
    protected $sate;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var SalesRepOrderRepository
     */
    protected $orderFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Order constructor.
     * @param OrderCreatedByAdmin $orderCreatedByAdmin
     * @param LoggerInterface $logger
     * @param RequestInterface $request
     * @param State $sate
     * @param CartRepositoryInterface $quoteRepository
     * @param Customer $customer
     * @param SalesRepOrderRepository $orderFactory
     * @param Data $helper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        OrderCreatedByAdmin $orderCreatedByAdmin,
        LoggerInterface $logger,
        RequestInterface $request,
        State $sate,
        CartRepositoryInterface $quoteRepository,
        Customer $customer,
        SalesRepOrderRepository $orderFactory,
        Data $helper,
        ManagerInterface $messageManager
    ) {
        $this->orderCreatedByAdmin = $orderCreatedByAdmin;
        $this->request = $request;
        $this->logger = $logger;
        $this->sate = $sate;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        $this->customer = $customer;
        $this->orderFactory = $orderFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Set Sales Representative
     *
     * @param Observer $observer
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        try {
            $this->saveTableOrderCreatedByAdmin($order);
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        $data = $order->getData();
        if ($this->helper->isEnable()) {
            $size = $this->orderFactory->getSize($data['entity_id']);
            if (isset($data['customer_id']) && $data['customer_id'] != null && $size == 0) {
                $this->customer->load($data['customer_id']);
                $isSalesRep = $this->helper->getSalesRepId();
                $salesRepId = $this->customer->getBssSalesRepresentative();
                $model = $this->orderFactory->getByOrderId($data['entity_id']);
                if (isset($salesRepId)
                    && empty($model->getData()) && in_array($salesRepId, $isSalesRep)) {
                    if (!empty($salesRepId) || $salesRepId != 0) {
                        $model->setOrderId($data['entity_id']);
                        $model->setUserId($salesRepId);
                        try {
                            $model->save();
                        } catch (Exception $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * Save table order_created_by_admin
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function saveTableOrderCreatedByAdmin($order)
    {
        try {
            $orderId = $order->getEntityId();
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $remoteIp = $order->getRemoteIp();
            if ($remoteIp) {
                $createdByAdmin = false;
            } else {
                $createdByAdmin = true;
            }
            if ($quote->getQuoteExtension() && $this->sate->getAreaCode() == "adminhtml") {
                $createdByAdmin = true;
            }
            $this->orderCreatedByAdmin->saveTableOrderCreatedByAdmin($orderId, $createdByAdmin);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
