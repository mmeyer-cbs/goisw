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

use Bss\QuoteExtension\Controller\Quote;
use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PrintAction
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class PrintAction extends Quote
{
    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\PrintHelper
     */
    protected $helperData;
    /**
     * PrintAction constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerQuoteExtension $quoteExtension
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\PrintHelper $helperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerQuoteExtension $quoteExtension,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\QuoteExtension\Helper\QuoteExtension\PrintHelper $helperData
    ) {
        $this->helperData = $helperData;
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
    }

    /**
     * Print Pdf Action
     * @return ResponseInterface|Redirect|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $manaQuoteId = (int)$this->getRequest()->getParam('quote');
        $quoteId = (int)$this->getRequest()->getParam('quote_id');

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/quote/view', ['quote_id' => $manaQuoteId]);
        }

        if ($quoteId) {
            $quote = $this->helperData->getQuoteRepository()->get($quoteId);
            if ($quote) {
                $pdf = $this->helperData->getPrintPdf()->getPdf([$quote]);
                $date = $this->helperData->getDateTime()->date('Y-m-d_H-i-s');
                $fileContent = $pdf->render();

                return $this->helperData->getFileFactory()->create(
                    'quote' . $date . '.pdf',
                    $fileContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        }
    }
}
