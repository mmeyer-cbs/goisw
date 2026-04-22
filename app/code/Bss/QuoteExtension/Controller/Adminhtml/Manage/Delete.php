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
namespace Bss\QuoteExtension\Controller\Adminhtml\Manage;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\DeleteQuote;

/**
 * Class Delete Quote
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var DeleteQuote
     */
    protected $deleteQuote;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuote
     */
    protected $manageQuote;

    /**
     * Delete constructor.
     * @param DeleteQuote $deleteQuote
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     */
    public function __construct(
        \Bss\QuoteExtension\Model\DeleteQuote $deleteQuote,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->deleteQuote = $deleteQuote;
        $this->manageQuote = $manageQuote;
        parent::__construct($context);
    }

    /**
     * Rejected action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('entity_id');

        if ($id) {
            try {
                $this->manageQuote->load($id);
                if ($this->manageQuote->getId() && $this->manageQuote->getStatus() != Status::STATE_ORDERED) {
                    $this->deleteQuote->saveQEOld($this->manageQuote);
                    $this->manageQuote->delete();
                    $this->messageManager->addSuccessMessage(__('You deleted the quote'));
                    return $resultRedirect->setPath('*/*/index');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->manageQuote->getId()]);
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a quote.'));
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
        return $this->_authorization->isAllowed('Bss_QuoteExtension::delete_quote');
    }
}
