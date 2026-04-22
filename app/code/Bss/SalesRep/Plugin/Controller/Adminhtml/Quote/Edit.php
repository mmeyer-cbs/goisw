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
namespace Bss\SalesRep\Plugin\Controller\Adminhtml\Quote;

use Braintree\Exception\Authorization;
use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Model\ResourceModel\ManageQuote\Collection;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Edit
 *
 * @package Bss\SalesRep\Plugin\Controller\Adminhtml\Quote
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Edit
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $redirect;

    /**
     * @var Collection
     */
    protected $collectionQuotes;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Authorization
     */
    protected $authorization;

    /**
     * Edit constructor.
     * @param Session $session
     * @param Collection $collectionQuotes
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirect
     * @param Data $helper
     * @param RequestInterface $request
     * @param \Magento\Framework\Authorization $authorization
     */
    public function __construct(
        Session $session,
        Collection $collectionQuotes,
        ManagerInterface $messageManager,
        RedirectFactory $redirect,
        Data $helper,
        RequestInterface $request,
        \Magento\Framework\Authorization $authorization
    ) {
        $this->session = $session;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->collectionQuotes = $collectionQuotes;
        $this->request = $request;
        $this->authorization = $authorization;
    }

    /**
     * Check quote assign Sales Rep
     *
     * @param $subject
     * @param $result
     * @return Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result)
    {
        $quoteAllowed = $this->authorization->isAllowed('Bss_QuoteExtension::quote_extension');
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep() && !$quoteAllowed) {
            $id = $this->request->getParam('entity_id');
            if ($user = $this->session->getUser()) {
                $resultRedirect = $this->redirect->create();
                $userId = $user->getId();
                $orders = $this->collectionQuotes
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('main_table.user_id', $userId)
                    ->addFieldToFilter('main_table.entity_id', $id)
                    ->getData();
                if (empty($orders)) {
                    $this->messageManager->addErrorMessage(__('Something went wrong while view the quote.'));
                    $resultRedirect->setPath('salesrep/index/quotes');
                    return $resultRedirect;
                }
            }
        }
        return $result;
    }
}
