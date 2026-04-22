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

namespace Bss\CompanyAccount\Controller\Order;

use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface as SubUserQuoteRepo;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\EmailHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Approve
 *
 * @package Bss\CompanyAccount\Controller\Order
 */
class Approve extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var SubUserQuoteRepo
     */
    protected $subUserQuoteRepo;

    /**
     * @var EmailHelper
     */
    protected $emailHelper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Function construct Approve
     *
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param SubUserQuoteRepo $subUserQuoteRepo
     * @param EmailHelper $emailHelper
     * @param Session $customerSession
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context               $context,
        ManagerInterface      $messageManager,
        SubUserQuoteRepo      $subUserQuoteRepo,
        EmailHelper           $emailHelper,
        Session               $customerSession,
        Data                  $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->messageManager = $messageManager;
        $this->subUserQuoteRepo = $subUserQuoteRepo;
        $this->emailHelper = $emailHelper;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Function action order (Approve/Reject)
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function actionOrder()
    {
        $actionOrder = $this->getRequest()->getParam('action');
        $order_id = $this->getRequest()->getParam('order_id');
        $subQuote = $this->subUserQuoteRepo->getByQuoteId($order_id);
        $subUser = $this->customerSession->getSubUser();
        if ($subUser == null) {
            $subQuote->setActionBy('0');
        } else {
            $subQuote->setActionBy($subUser->getSubId());
        }
        if ($this->helper->isSendMail(Data::XML_PATH_EMAIL_ORDER_ENABLED)
            && $this->helper->isSendEmailEnable('order')
        ) {
            $messageEmail = $this->emailHelper->sendOrderStatus($order_id, $subQuote->getSubId(), $actionOrder);
            if ($messageEmail) {
                $this->messageManager->addErrorMessage(__($messageEmail));
            }
        }
        if ($actionOrder == 'approve') {
            $subQuote->setQuoteStatus('approved');
            $this->subUserQuoteRepo->save($subQuote);
            $this->messageManager->addSuccessMessage(__("Order ID #" . $order_id . " is approved!"));
        } else {
            $subQuote->setQuoteStatus('rejected');
            $this->subUserQuoteRepo->save($subQuote);
            $this->messageManager->addSuccessMessage(__("Order ID #" . $order_id . " is rejected!"));
        }
    }

    /**
     * Function approve execute
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->actionOrder();
        $redirect = $this->resultRedirectFactory->create();
        $baseUrl = $this->_url->getUrl('sales/order/history', ['tab' => 'waiting']);
        $redirect->setUrl($baseUrl);
        return $redirect;
    }
}
