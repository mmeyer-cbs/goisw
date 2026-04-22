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
use Bss\SalesRep\Model\ResourceModel\Auth\Auth;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Session
 *
 * @package Bss\SalesRep\Observer\Model
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Session implements ObserverInterface
{
    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var Collection
     */
    protected $customerCollection;

    /**
     * @var \Bss\SalesRep\Model\ResourceModel\SalesRepOrder\Collection
     */
    protected $orderCollection;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $adminSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Auth
     */
    protected $rules;

    /**
     * Session constructor.
     * @param Auth $rules
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param SessionManagerInterface $coreSession
     * @param Collection $customerCollection
     * @param \Bss\SalesRep\Model\ResourceModel\SalesRepOrder\Collection $orderCollection
     * @param Data $helper
     */
    public function __construct(
        Auth $rules,
        \Magento\Backend\Model\Auth\Session $adminSession,
        SessionManagerInterface $coreSession,
        Collection $customerCollection,
        \Bss\SalesRep\Model\ResourceModel\SalesRepOrder\Collection $orderCollection,
        Data $helper
    ) {
        $this->rules = $rules;
        $this->adminSession = $adminSession;
        $this->coreSession = $coreSession;
        $this->customerCollection = $customerCollection;
        $this->orderCollection = $orderCollection;
        $this->helper = $helper;
    }

    /**
     * Set arrays to session admin
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $userId = $observer->getEvent()->getUser()->getUserId();
            $salesRep = $this->helper->getSalesRepId();
            if (in_array($userId, $salesRep)) {
                $roleId = $this->adminSession->getUser()->getRole()->getId();
                // $this->rules->updateRule($roleId);
            }

            /**
             * Set array Customers Id into Admin Session
             */
            $customerCollection = $this->customerCollection
                ->addFieldToSelect('*')
                ->addAttributeToFilter('bss_sales_representative', $userId);
            $customerIds = [];
            foreach ($customerCollection as $customer) {
                $customerIds[] = $customer['entity_id'];
            }
            $this->coreSession->setCustomerIds($customerIds);

            /**
             * Set array Orders Id into Admin Session
             */
            $orderCollection = $this->orderCollection
                ->addFieldToSelect('*')
                ->addFieldToFilter('user_id', $userId);
            $orderIds = [];
            foreach ($orderCollection as $order) {
                $orderIds[] = $order['order_id'];
            }
            $this->coreSession->setOrderIds($orderIds);

            /**
             * Set array User Id is Sales Rep into Admin Session
             */
            $idSalesRep = $this->helper->getSalesRepId();
            $this->coreSession->setIsSalesRep($idSalesRep);
        }
    }
}
