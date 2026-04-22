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
namespace Bss\SalesRep\Plugin\Controller\Adminhtml\Order;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Model\ResourceModel\SalesRepOrder\Collection;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Authorization;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Controller\Adminhtml\Order;

/**
 * Class View
 *
 * @package Bss\SalesRep\Plugin\Controller\Adminhtml\Order
 */
class View
{
    /**
     * @var \Bss\SalesRep\Model\ConnectDB
     */
    protected $connectDB;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RedirectFactory
     */
    protected $redirect;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Collection
     */
    protected $collectionOrder;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Authorization
     */
    protected $authorization;

    /**
     * Edit constructor.
     * @param Session $session
     * @param Collection $collectionOrder
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirect
     * @param Data $helper
     * @param RequestInterface $request
     * @param Authorization $authorization
     */
    public function __construct(
        \Bss\SalesRep\Model\ConnectDB $connectDB,
        Session $session,
        Collection $collectionOrder,
        ManagerInterface $messageManager,
        RedirectFactory $redirect,
        Data $helper,
        RequestInterface $request,
        Authorization $authorization
    ) {
        $this->connectDB = $connectDB;
        $this->session = $session;
        $this->collectionOrder = $collectionOrder;
        $this->helper = $helper;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->authorization = $authorization;
    }

    /**
     * Check customer assigned User is Sales Rep.
     *
     * @param Order $subject
     * @param Page $result
     * @return Page|\Magento\Backend\Model\View\Result\Redirect|Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        Order $subject,
        $result
    ) {
        $customerAllowed = $this->authorization->isAllowed('Magento_Sales::sales');
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep() && !$customerAllowed) {
            $user = $this->session->getUser();
            if ($user) {
                $userId = $user->getId();
                $id = $this->request->getParam('order_id');
                if ($id) {
                    $orders = $this->collectionOrder
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('user_id', $userId)
                        ->addFieldToFilter('order_id', $id)
                        ->getData();
                    if (empty($orders)) {
                        return $this->redirectSalesRep(__("Something went wrong while view the order."));
                    }
                }
                $addressId = $this->request->getParam('address_id');
                if ($addressId) {
                    if(!$this->connectDB->isAddressOfUser($addressId, $userId)) {
                        return $this->redirectSalesRep(__("Something went wrong while edit address."));
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Set permission salesrep
     *
     * @param Order $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    /**
     * Check sales rep and can comment
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View\History $subject
     * @param bool $result
     * @return bool|mixed
     */
    public function beforeExecute($subject)
    {
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep()) {
            $this->helper->setIsAllowed("Magento_Sales::emails");
            $this->helper->setIsAllowed("Magento_LoginAsCustomer::login");
        }
        return $subject;
    }

    /**
     * Redirect page sales rep order
     *
     * @param string $message
     * @return Redirect
     */
    private function redirectSalesRep($message)
    {
        $resultRedirect = $this->redirect->create();
        $this->messageManager->addErrorMessage($message);
        $resultRedirect->setPath('salesrep/index/order');
        return $resultRedirect;
    }

}
