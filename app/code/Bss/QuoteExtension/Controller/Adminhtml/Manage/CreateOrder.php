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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Controller\Adminhtml\Manage;

use Bss\QuoteExtension\Model\QuoteCustomerGroupId;

/**
 * Class CreateOrder
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class CreateOrder extends \Magento\Backend\App\Action
{
    /**
     * @var QuoteCustomerGroupId
     */
    protected $quoteCustomerGroupId;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuote
     */
    protected $manageQuote;

    /**
     * CreateOrder constructor.
     * @param QuoteCustomerGroupId $quoteCustomerGroupId
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     */
    public function __construct(
        \Bss\QuoteExtension\Model\QuoteCustomerGroupId $quoteCustomerGroupId,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote
    ) {
        $this->quoteCustomerGroupId = $quoteCustomerGroupId;
        parent::__construct($context);
        $this->sessionQuote   = $sessionQuote;
        $this->quoteRepository = $quoteRepository;
        $this->manageQuote = $manageQuote;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $quote = $this->quoteRepository->get($this->getRequest()->getParam('quote_id'));
                $this->quoteCustomerGroupId->area = "backend";
                $quote = $this->quoteCustomerGroupId->getQuoteView($quote, $quote->getCustomerId(), null);
                $this->sessionQuote->setCustomerId($quote->getCustomerId());
                $this->sessionQuote->setQuoteId($quote->getId());

                // go to grid
                $resultRedirect->setUrl($this->getUrl('sales/order_create/index'));

                return $resultRedirect;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a quote to create order.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_QuoteExtension::create_order');
    }
}
