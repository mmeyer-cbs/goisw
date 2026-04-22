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

use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as QuoteExtensionCollection;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class Edit
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edit extends Action
{
    /**
     * @var \Bss\QuoteExtension\Model\Customer
     */
    protected $customerQuoteExtension;

    /**
     * @var \Bss\QuoteExtension\Model\QuoteCustomerGroupId
     */
    protected $quoteCustomerGroupId;

    /**
     * @var QuoteExtensionCollection
     */
    protected $quoteExtensionCollection;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var ManageQuote
     */
    protected $manageQuote;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObject;

    /**
     * @var Quote
     */
    protected $newQuote;

    /**
     * Edit constructor.
     * @param \Bss\QuoteExtension\Model\Customer $customerQuoteExtension
     * @param \Bss\QuoteExtension\Model\QuoteCustomerGroupId $quoteCustomerGroupId
     * @param QuoteExtensionCollection $quoteExtensionCollection
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param Session $backendSession
     * @param ManageQuote $manageQuote
     * @param DataObjectFactory $dataObject
     * @param QuoteFactory $quoteFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\QuoteExtension\Model\Customer $customerQuoteExtension,
        \Bss\QuoteExtension\Model\QuoteCustomerGroupId $quoteCustomerGroupId,
        QuoteExtensionCollection $quoteExtensionCollection,
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        CartRepositoryInterface $quoteRepository,
        Session $backendSession,
        ManageQuote $manageQuote,
        DataObjectFactory $dataObject,
        QuoteFactory $quoteFactory
    ) {
        $this->customerQuoteExtension = $customerQuoteExtension;
        $this->quoteCustomerGroupId = $quoteCustomerGroupId;
        $this->quoteExtensionCollection = $quoteExtensionCollection;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->quoteRepository = $quoteRepository;
        $this->backendSession = $backendSession;
        $this->manageQuote = $manageQuote;
        $this->dataObject = $dataObject;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Execute Edit
     *
     * @return Page|ResponseInterface|Redirect|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');

        if ($id) {
            $quoteExtension = $this->quoteExtensionCollection->create()
                ->addFieldToFilter('main_table.entity_id', $id)->getLastItem();
            $this->customerQuoteExtension->resetCustomerId($quoteExtension);
            $this->manageQuote = $quoteExtension;
            if ($this->manageQuote->getId()) {
                $this->initRuleData();
                $data = $this->backendSession->getFormData(true);
                if (!empty($data)) {
                    $this->manageQuote->setData($data);
                }

                $checkFirstEdit = 0;
                if (!$this->manageQuote->getTargetQuote()) {
                    $checkFirstEdit = 1;
                    $this->setTargetQuote();
                } else {
                    $this->quoteCustomerGroupId->saveQuoteObserver($this->manageQuote->getTargetQuote(), $this->manageQuote->getCustomerId());
                }

                if (!$this->manageQuote->getBackendQuoteId()) {
                    $checkFirstEdit = 1;
                    $this->setBackendQuoteId();
                }

                $this->newQuote = $this->quoteRepository->get($this->manageQuote->getBackendQuoteId());
                if (!$checkFirstEdit) {
                    $this->customerQuoteExtension->changeCustomerEmail($this->newQuote);
                    $this->newQuote = $this->quoteCustomerGroupId->getQuoteView($this->newQuote, $this->manageQuote->getCustomerId(), $this->manageQuote);
                }

                $this->backendSession->setQuoteExtensionId($this->newQuote->getId());
                $this->backendSession->setQuoteManageId($id);
                $this->coreRegistry->register('mage_quote', $this->newQuote);
                $this->coreRegistry->register('quoteextension_quote', $this->manageQuote);

                /** @var Page $resultPage */
                $resultPage = $this->resultPageFactory->create();

                $resultPage->setActiveMenu('Magento_Sales::sales')
                    ->addBreadcrumb(__('Request4Quote'), __('Request4Quote'))
                    ->addBreadcrumb(__('Quote'), __('Quote'));
                $resultPage->addBreadcrumb(__('Edit Quote'), __('Edit Quote'));
                $resultPage->getConfig()->getTitle()->prepend(__('Request4Quote'));
                $resultPage->getConfig()->getTitle()->prepend('#' . $this->manageQuote->getIncrementId());
                return $resultPage;
            }
        }
        $this->messageManager->addErrorMessage(__('This Quote no longer exists'));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Set Rule Data in page
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function initRuleData()
    {
        $mageQuote = $this->quoteRepository->get($this->manageQuote->getQuoteId());
        $this->quoteCustomerGroupId->area = "backend";
        $mageQuote = $this->quoteCustomerGroupId->getQuoteView($mageQuote, $mageQuote->getCustomerId(), $this->manageQuote);
        $this->customerQuoteExtension->changeCustomerEmail($mageQuote);
        $object = $this->dataObject->create();
        $object->setData(
            [
                'store_id' => $mageQuote->getStore()->getId(),
                'website_id' => $mageQuote->getStore()->getWebsiteId(),
                'customer_group_id' => $mageQuote->getCustomerGroupId()
            ]
        );
        $this->coreRegistry->register(
            'rule_data',
            $object
        );

        return $this;
    }

    /**
     * { @inheritdoc }
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_QuoteExtension::edit_quote');
    }

    /**
     * Set Target Quote
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    private function setTargetQuote()
    {
        $mageQuote = $this->quoteRepository->get($this->manageQuote->getQuoteId());
        if ($this->quoteCustomerGroupId->canChangeQuote($this->manageQuote)) {
            $this->quoteCustomerGroupId->setCustomerGroupId($mageQuote);
        }
        $this->newQuote = $this->quoteFactory->create();
        $mageQuote->cloneQuoteExtension($this->newQuote);
        $this->quoteRepository->save($this->newQuote);
        $this->customerQuoteExtension->changeCustomerEmail($this->newQuote);
        $this->_eventManager->dispatch(
            'quote_extension_clone_after',
            ['quote' => $this->newQuote]
        );
        $quoteId = $this->newQuote->getId();
        $this->newQuote = $this->quoteRepository->get($quoteId);
        $this->manageQuote->setNotSendEmail(true);
        $this->manageQuote->setTargetQuote($this->newQuote->getId())->save();
        return $this;
    }

    /**
     * Get Extension Draft
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    private function setBackendQuoteId()
    {
        $mageQuote = $this->quoteRepository->get($this->manageQuote->getTargetQuote());
        if ($this->quoteCustomerGroupId->canChangeQuote($this->manageQuote)) {
            $this->quoteCustomerGroupId->setCustomerGroupId($mageQuote);
        }
        $this->newQuote = $this->quoteFactory->create();
        $mageQuote->cloneQuoteExtension($this->newQuote);
        $this->quoteRepository->save($this->newQuote);
        $this->customerQuoteExtension->changeCustomerEmail($this->newQuote);
        $this->_eventManager->dispatch(
            'quote_extension_clone_after',
            ['quote' => $this->newQuote]
        );
        $quoteId = $this->newQuote->getId();
        $this->newQuote = $this->quoteRepository->get($quoteId);
        $this->manageQuote->setNotSendEmail(true);
        $this->manageQuote->setBackendQuoteId($this->newQuote->getId())->save();
        return $this;
    }
}
