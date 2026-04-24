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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Controller\Adminhtml\Manage;

use Exception;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Class Save
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends AbstractController
{
    /**
     * @var Date
     */
    protected $filterDate;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\Version $quoteVersion
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        Date $filterDate,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bss\QuoteExtension\Helper\Data $helper,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        \Bss\QuoteExtension\Helper\QuoteExtension\Version $quoteVersion,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->filterDate = $filterDate;
        parent::__construct(
            $context,
            $quoteRepository,
            $helper,
            $manageQuote,
            $quoteVersion,
            $backendSession,
            $quoteFactory
        );
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('quote_manage_id');
        if ($id) {
            try {
                $data = $this->getRequest()->getPostValue();
                $this->manageQuote->load($id);

                if ($this->manageQuote->getId()) {
                    $mageQuote = $this->getQuote();
                    $mageQuote->setLogComment($data['customer_note']);
                    $mageQuote->setAreaLog('admin');
                    $version = $this->manageQuote->getVersion();
                    if ($this->backendSession->getHasChange() || $data['customer_note']) {
                        $this->manageQuote->setQuoteIdNotComment($this->manageQuote->getQuoteId());
                        $this->quoteVersion->setDataToQuoteVersion($mageQuote, $this->manageQuote);
                        $version++;
                        $this->backendSession->setHasChange(false);
                    }
                    $this->manageQuote->setNotSendEmail(true);
                    $targetQuote = $this->manageQuote->getTargetQuote();
                    $oldQuote = $this->manageQuote->getOldQuote();
                    $oldQuote = $oldQuote . ',' . $targetQuote;
                    $oldQuote = ltrim($oldQuote, ",");
                    $backendQuote = $this->manageQuote->getBackendQuoteId();
                    $this->manageQuote->setBackendQuoteId(null);
                    $this->manageQuote->setTargetQuote($backendQuote);
                    $data['expiry'] = $this->helper->convertDateExpired($this->convertDateExpiryLocale($data['expiry']));
                    $this->manageQuote->setData('expiry', $data["expiry"]);
                    $this->manageQuote->setData('status', $data['status']);
                    $this->manageQuote->setData('version', $version);
                    $this->manageQuote->setData('old_quote', $oldQuote);
                    $this->manageQuote->save();
                    $this->messageManager->addSuccessMessage(__('You saved the quote'));
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->manageQuote->getId()]);
                }
            } catch (Exception $e) {
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
     * Convert Date Expiry Locale
     *
     * @param string|null $dateExpiry
     * @return string|null
     * @throws Exception
     */
    public function convertDateExpiryLocale($dateExpiry)
    {
        if ($dateExpiry) {
            return $this->filterDate->filter($dateExpiry);
        }
        return $dateExpiry;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_QuoteExtension::save_quote');
    }
}
