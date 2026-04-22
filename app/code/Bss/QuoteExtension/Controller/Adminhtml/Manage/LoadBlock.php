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

use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as QuoteExtensionCollection;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Helper\Product;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class LoadBlock
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadBlock extends Edit
{
    /**
     * @var
     */
    protected $quoteExtension;

    /**
     * @var
     */
    protected $manaQuoteExtension;

    /**
     * @var \Bss\QuoteExtension\Helper\Admin\Edit\LoadBlock
     */
    protected $loadBlock;

    /**
     * LoadBlock constructor.
     * @param Product $productHelper
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
     * @param \Bss\QuoteExtension\Helper\Admin\Edit\LoadBlock $loadBlock
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Helper\Product $productHelper,
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
        QuoteFactory $quoteFactory,
        \Bss\QuoteExtension\Helper\Admin\Edit\LoadBlock $loadBlock
    ) {
        $productHelper->setSkipSaleableCheck(true);
        $this->loadBlock = $loadBlock;
        parent::__construct(
            $customerQuoteExtension,
            $quoteCustomerGroupId,
            $quoteExtensionCollection,
            $context,
            $coreRegistry,
            $resultPageFactory,
            $quoteRepository,
            $backendSession,
            $manageQuote,
            $dataObject,
            $quoteFactory
        );
    }

    /**
     * Loading page block
     *
     * @return Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $request = $this->getRequest();

        $asJson = $request->getParam('json');
        $block = $request->getParam('block');

        try {
            $this->initRuleData();
            $this->processActionData();
        } catch (Exception $e) {
            $this->reloadQuote();
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
        $this->coreRegistry->register('mage_quote', $this->getQuote());
        $this->coreRegistry->register('quoteextension_quote', $this->getManaQuote());
        $resultPage = $this->resultPageFactory->create();
        if ($asJson) {
            $resultPage->addHandle('sales_order_create_load_block_json');
        } else {
            $resultPage->addHandle('sales_order_create_load_block_plain');
        }

        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }

            foreach ($blocks as $block) {
                $resultPage->addHandle('bss_quote_extension_load_block_' . $block);
            }
        }
        $result = $resultPage->getLayout()->renderElement('content');
        return $this->loadBlock->setContent($result);
    }

    protected function initRuleData()
    {
        $mageQuote = $this->getQuote();
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
     * Save Quote
     *
     * return $this
     */
    protected function processActionData()
    {
        $this->loadBlock->createQuote()->setQuote($this->getQuote());
        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', []);
            $items = $this->processFiles($items);
            $this->loadBlock->createQuote()->updateQuoteItems($items);
            $this->backendSession->setHasChange(true);
        }

        if ($this->getRequest()->has('item') && !$this->getRequest()->getPost('update_items')
        ) {
            $items = $this->getRequest()->getPost('item');
            $items = $this->processFiles($items);
            $this->loadBlock->createQuote()->addProducts($items);
            $this->backendSession->setHasChange(true);
        }
        if ($this->getRequest()->has('quote')
        ) {
            $quote = $this->getRequest()->getPost('quote');
            if (isset($quote['quote_shipping_price'])) {
                $this->loadBlock->createQuote()->getQuote()->setData('quote_shipping_price', $quote['quote_shipping_price']);
            }
            if (isset($quote['shipping_method'])) {
                $this->loadBlock->createQuote()->setShippingMethod($quote['shipping_method']);
            }
            $this->backendSession->setHasChange(true);
        }
        /**
         * Remove quote item
         */
        $removeItemId = (int)$this->getRequest()->getPost('remove_item');
        $removeFrom = (string)$this->getRequest()->getPost('from');
        if ($removeItemId && $removeFrom) {
            $this->loadBlock->createQuote()->removeItem($removeItemId, $removeFrom);
            $this->backendSession->setHasChange(true);
        }
        $this->loadBlock->createQuote()->collectShippingRates();
        $this->loadBlock->createQuote()->setRecollect(true);
        $this->loadBlock->createQuote()->saveQuote();
        return $this;
    }

    /**
     * Process buyRequest file options of items
     *
     * @param array $items
     * @return array
     */
    protected function processFiles($items)
    {
        foreach ($items as $id => $item) {
            $buyRequest = $this->loadBlock->initDataObject($item);
            $params = ['files_prefix' => 'item_' . $id . '_'];
            $buyRequest = $this->loadBlock->getInfoBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }
        return $items;
    }

    /**
     * Load Quote By Quote Id
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function reloadQuote()
    {
        $quoteId = $this->backendSession->getQuoteExtensionId();
        $this->getQuote()->load($quoteId);
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @throws NoSuchEntityException
     */
    protected function getQuote()
    {
        if (!$this->quoteExtension) {
            $quoteId = $this->getRequest()->getPost('quote_id')
                ? $this->getRequest()->getPost('quote_id') : $this->backendSession->getQuoteExtensionId();
            $this->quoteExtension = $this->quoteRepository->get($quoteId);
        }
        return $this->quoteExtension;
    }

    /**
     * Set Quote
     *
     * @param CartInterface $quote
     * @return $this
     */
    protected function setQuote($quote)
    {
        $this->quoteExtension = $quote;
        return $this;
    }

    /**
     * Get Request Quote
     *
     * @return ManageQuote
     */
    protected function getManaQuote()
    {
        if (!$this->manaQuoteExtension) {
            $quoteManageId = $this->backendSession->getQuoteManageId();
            $this->manaQuoteExtension = $this->manageQuote->load($quoteManageId);
        }
        return $this->manaQuoteExtension;
    }
}
