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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Helper\Mail as HelperMail;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Helper\Data as HelperData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class ViewSubmit
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class ReSendEmail extends Action
{
    protected $helperData;
    /**
     * @var HelperMail
     */
    protected $helperMail;
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var Validator
     */
    protected $formKeyValidator;
    /**
     * @var ManageQuote
     */
    protected $manageQuote;

    /**
     * ReSendEmail constructor.
     * @param HelperData $helperData
     * @param HelperMail $helperMail
     * @param CartRepositoryInterface $quoteRepository
     * @param Validator $formKeyValidator
     * @param ManageQuote $manageQuote
     * @param Context $context
     */
    public function __construct(
        HelperData $helperData,
        HelperMail $helperMail,
        CartRepositoryInterface $quoteRepository,
        Validator $formKeyValidator,
        ManageQuote $manageQuote,
        Context $context
    ) {
        $this->helperData = $helperData;
        $this->helperMail = $helperMail;
        $this->quoteRepository = $quoteRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->manageQuote = $manageQuote;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
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
                $currentTime = $this->helperData->getCurrentDateTime();
                $oldQuote = $this->processOldQuote();
                $this->manageQuote->setTargetQuote(null);
                $this->manageQuote->setBackendQuoteId(null);
                $this->manageQuote->setStatus(Status::STATE_RESUBMIT);
                $this->manageQuote->setVersion($manageQuote->getVersion() + 1);
                $this->manageQuote->setUpdatedAt($currentTime);
                $this->manageQuote->setOldQuote($oldQuote);
                $this->manageQuote->setData('email_sent', "0");
                $this->manageQuote->save();

                $quote = $this->quoteRepository->get($manageQuote->getQuoteId());
                $this->helperMail->sendNotificationQuoteReSubmitEmail($quote, $this->manageQuote);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->resultRedirectFactory
                    ->create()
                    ->setPath('quoteextension/quote/view/quote_id/' . $params['request_entity_id']);
            }
        }
        $this->messageManager->addSuccessMessage(__('You resubmit the quote'));
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($params['token'])) {
            return $resultRedirect->setPath(
                'quoteextension/quote/view',
                [
                    'quote_id' => $params['request_entity_id'],
                    'token' => $params['token']
                ]
            );
        }
        return $resultRedirect->setPath(
            'quoteextension/quote/view',
            [
                'quote_id' => $params['request_entity_id']
            ]);
    }

    /**
     * Process old quote to request quote
     *
     * @return string
     */
    private function processOldQuote()
    {
        $oldQuote = $this->manageQuote->getOldQuote();
        $quoteId = $this->manageQuote->getQuoteId();
        $targetQuote = $this->manageQuote->getTargetQuote();
        $backendQuote = $this->manageQuote->getBackendQuoteId();
        if ($targetQuote && $quoteId != $targetQuote) {
            $oldQuote = $oldQuote . ',' . $targetQuote;
        }
        if ($backendQuote && $quoteId != $backendQuote) {
            $oldQuote = $oldQuote . ',' . $backendQuote;
        }
        if ($oldQuote) {
            return ltrim($oldQuote, ",");
        }
        return "";
    }
}
