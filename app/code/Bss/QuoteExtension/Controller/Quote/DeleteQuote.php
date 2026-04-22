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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Helper\Data as HelperData;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ManageQuote;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Class DeleteQuote
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class DeleteQuote extends Action
{
    /**
     * @var \Bss\QuoteExtension\Model\DeleteQuote
     */
    protected $deleteQuote;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Validator
     */
    protected $formKeyValidator;
    /**
     * @var ManageQuote
     */
    protected $manageQuote;

    /**
     * DeleteQuote constructor.
     * @param HelperData $helperData
     * @param Validator $formKeyValidator
     * @param ManageQuote $manageQuote
     * @param Context $context
     */
    public function __construct(
        \Bss\QuoteExtension\Model\DeleteQuote $deleteQuote,
        \Magento\Customer\Model\SessionFactory $customerSession,
        HelperData $helperData,
        Validator $formKeyValidator,
        ManageQuote $manageQuote,
        Context $context
    ) {
        $this->deleteQuote = $deleteQuote;
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->manageQuote = $manageQuote;
        parent::__construct($context);
    }

    /**
     * Delete request for quote
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->helperData->isEnable()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/*');
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/history');
        }

        $params = $this->getRequest()->getParams();
        if (isset($params['request_entity_id'])) {
            try {
                $manageQuote = $this->manageQuote->load($params['request_entity_id']);
                if (!$manageQuote->getQuoteId()) {
                    $this->messageManager->addErrorMessage(__('We can\'t find a quote.'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/history');
                }

                if (!$this->checkCustomer($manageQuote) || $this->manageQuote->getStatus() == Status::STATE_ORDERED) {
                    $this->messageManager->addErrorMessage(__('We don\'t delete the quote.'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/history');
                }
                $this->deleteQuote->saveQEOld($this->manageQuote);
                $this->manageQuote->delete();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->resultRedirectFactory
                    ->create()
                    ->setPath('quoteextension/quote/view/quote_id/' . $params['request_entity_id']);
            }
        }
        $this->messageManager->addSuccessMessage(__('You deleted the quote'));
        return $this->resultRedirectFactory->create()->setPath('quoteextension/quote/history');
    }

    /**
     * Check quote extension of quote
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $quoteExtension
     * @return bool
     */
    public function checkCustomer($quoteExtension)
    {
        $customerId = $this->customerSession->create()->getCustomerId();
        if ($customerId == $quoteExtension->getCustomerId()) {
            return true;
        }
        return false;
    }
}
