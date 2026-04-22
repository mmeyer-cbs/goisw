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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class PrintAction
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class PrintAction extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_QuoteExtension::quote';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Bss\QuoteExtension\Model\PrintAction
     */
    protected $modelPdf;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * PrintAction constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Bss\QuoteExtension\Model\Pdf\PrintPdf $modelPdf
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bss\QuoteExtension\Model\Pdf\PrintPdf $modelPdf,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->fileFactory = $fileFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->quoteRepository = $quoteRepository;
        $this->modelPdf = $modelPdf;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * Print Execute
     *
     * @return \Magento\Backend\Model\View\Result\Forward|ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        if ($quoteId) {
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote) {
                $pdf = $this->modelPdf->getPdf([$quote]);
                $date = $this->dateTime->date('Y-m-d_H-i-s');
                $fileContent = $pdf->render();

                return $this->fileFactory->create(
                    'quote' . $date . '.pdf',
                    $fileContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        }

        return $this->resultForwardFactory->create()->forward('noroute');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_QuoteExtension::print_quote');
    }
}
