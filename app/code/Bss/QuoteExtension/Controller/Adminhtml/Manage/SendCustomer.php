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

use Bss\QuoteExtension\Model\Config\Source\Status;

/**
 * Class SendCustomer
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class SendCustomer extends AbstractController
{
    /**
     * Send Customer action
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
                if ($this->manageQuote->getId()) {
                    $token = $this->helper->generateRandomString(30);
                    $this->manageQuote->setToken($token);
                    $this->manageQuote->setData('status', Status::STATE_UPDATED);
                    $this->manageQuote->setData('email_sent', "1");
                    $targetQuote = $this->manageQuote->getTargetQuote();
                    $quoteId = $this->manageQuote->getQuoteId();
                    $oldQuote = $this->manageQuote->getOldQuote();
                    $oldQuote = $oldQuote . ',' . $quoteId;
                    $oldQuote = ltrim($oldQuote, ",");
                    $this->manageQuote->setMoveCheckout(1);
                    $this->manageQuote->setQuoteId($targetQuote);
                    $this->manageQuote->setOldQuote($oldQuote);
                    $this->manageQuote->setIsAdminSubmitted(1);
                    $this->manageQuote->save();
                    $this->_eventManager->dispatch(
                        'quote_extension_save_after_send',
                        [
                            'quoteExtension' => $this->manageQuote
                        ]
                    );
                    $this->messageManager->addSuccessMessage(__('You updated the quote, has send email to customer'));
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
        return $this->_authorization->isAllowed('Bss_QuoteExtension::accept_quote');
    }
}
