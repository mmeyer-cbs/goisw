<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\Order;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\Sales\Block\Items\AbstractItems;
use Magento\Theme\Block\Html\Pager;

/**
 * Sales order view items block.
 *
 * @api
 * @since 100.0.2
 */
class Items extends AbstractItems
{
    /**
     * Order items per page.
     *
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Collection|null
     */
    private $itemCollection;

    /**
     * @var Currency
     */
    protected $quoteCurrency;

    /**
     * @param Context $context
     * @param QuoteFactory $quoteFactory
     * @param CollectionFactory|null $itemCollectionFactory
     * @param Currency $quoteCurrency
     * @param array $data
     */
    public function __construct(
        Context           $context,
        QuoteFactory      $quoteFactory,
        CollectionFactory $itemCollectionFactory = null,
        Currency          $quoteCurrency,
        array             $data = []
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->itemCollectionFactory = $itemCollectionFactory ?: ObjectManager::getInstance()
            ->get(CollectionFactory::class);
        $this->quoteCurrency = $quoteCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Init pager block and item collection with page size and current page number
     *
     * @return $this
     * @since 100.1.7
     */
    protected function _prepareLayout()
    {
        $this->itemsPerPage = $this->_scopeConfig->getValue('sales/orders/items_per_page');
        $this->itemCollection = $this->createItemsCollection();
        /** @var Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        if ($pagerBlock) {
            $pagerBlock->getChildBlock('bss-action-order');
            $this->preparePager($pagerBlock);
        }
        return parent::_prepareLayout();
    }

    /**
     * Determine if the pager should be displayed for order items list.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return bool
     * @since 100.1.7
     */
    public function isPagerDisplayed()
    {
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        return $pagerBlock && ($this->itemCollection->getSize() > $this->itemsPerPage);
    }

    /**
     * Get visible items for current page.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return \Magento\Framework\DataObject[]
     * @since 100.1.7
     */
    public function getItems()
    {
        return $this->itemCollection->getItems();
    }

    /**
     * Get pager HTML according to our requirements.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return string HTML output
     * @since 100.1.7
     */
    public function getPagerHtml()
    {
        /** @var Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        return $pagerBlock ? $pagerBlock->toHtml() : '';
    }

    /**
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        return $this->quoteFactory->create()->load($quoteId);
    }

    /**
     * Prepare pager block
     *
     * @param AbstractBlock $pagerBlock
     */
    private function preparePager(AbstractBlock $pagerBlock): void
    {
        $collectionToPager = $this->createItemsCollection();
        $collectionToPager->addFieldToFilter('parent_item_id', ['null' => true]);
        $pagerBlock->setCollection($collectionToPager);
        $pagerBlock->setLimit($this->itemsPerPage);
        $pagerBlock->setAvailableLimit([$this->itemsPerPage]);
        $pagerBlock->setShowAmounts($this->isPagerDisplayed());
    }

    /**
     * Create items collection
     *
     * @return Collection
     */
    private function createItemsCollection(): Collection
    {
        $collection = $this->itemCollectionFactory->create();
        $quoteItem = $this->getQuote();
        $collection->addFieldToFilter('quote_id', $quoteItem['entity_id']);
        return $collection;
    }

    /**
     * Format total value based on quote currency
     *
     * @param \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatValue($total)
    {
        return $this->formatPrice($total);
    }

    /**
     * Function format price of quote
     *
     * @param $total
     * @param bool $addBrackets
     * @return string
     */
    public function formatPrice($total, $addBrackets = false): string
    {
        $quoteCurrency = $this->quoteCurrency;
        return $quoteCurrency->formatPrecision($total, 2, [], true, $addBrackets);
    }
}
