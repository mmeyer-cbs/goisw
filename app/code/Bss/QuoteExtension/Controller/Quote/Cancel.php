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
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Helper\Mail;
use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class Cancel
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Cancel extends \Bss\QuoteExtension\Controller\Quote
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Mail
     */
    private $mailHelper;

    /**
     * Cancel constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerQuoteExtension $quoteExtension
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @param CartRepositoryInterface $quoteRepository
     * @param Mail $mailHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerQuoteExtension $quoteExtension,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        CartRepositoryInterface $quoteRepository,
        Mail $mailHelper
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $quoteExtensionSession,
            $storeManager,
            $formKeyValidator,
            $quoteExtension,
            $manageQuote,
            $resultPageFactory
        );
        $this->quoteRepository = $quoteRepository;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Excute Function
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $quoteId = (int)$this->getRequest()->getParam('quote');
        $token = $this->getRequest()->getParam('token');

        $this->manageQuote->load($quoteId);
        $quote = $this->quoteRepository->get($this->manageQuote->getQuoteId());
        if ($this->manageQuote->getId()) {
            try {
                $this->manageQuote->setData('status', Status::STATE_CANCELED);
                $this->manageQuote->save();
                $this->mailHelper->sendNotificationQuoteCancelledEmail($quote, $this->manageQuote);
                $this->messageManager->addSuccessMessage(__('The quote has been cancelled!'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__("Can't cancel this quote!"));
            }
        } else {
            $this->messageManager->addErrorMessage(__("Can't cancel this quote!"));
        }
        $resultReidrect = $this->resultRedirectFactory->create();
        if ($token) {
            return $resultReidrect->setPath(
                'quoteextension/quote/view',
                [
                    'quote_id' => $quoteId,
                    'token' => $token
                ]
            );
        }
        return $resultReidrect->setPath(
            'quoteextension/quote/view',
            ['quote_id' => $quoteId]
        );
    }
}
