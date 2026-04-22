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
namespace Bss\QuoteExtension\Helper\QuoteExtension;

use Bss\QuoteExtension\Helper\Json as JsonHelper;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\QuoteItemFactory;
use Bss\QuoteExtension\Model\QuoteVersion;
use Bss\QuoteExtension\Model\ResourceModel\QuoteVersion\Collection as QuoteVersionCollection;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class Version
 *
 * @package Bss\QuoteExtension\Helper\QuoteExtension
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Version extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var QuoteVersion
     */
    protected $quoteVersion;

    /**
     * @var QuoteItemFactory
     */
    protected $quoteItem;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var QuoteVersionCollection
     */
    protected $quoteVersionCollection;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var Url
     */
    protected $catalogUrlBuilder;

    /**
     * Version constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param QuoteVersion $quoteVersion
     * @param QuoteItemFactory $quoteItem
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\App\State $state
     * @param QuoteVersionCollection $quoteVersionCollection
     * @param \Bss\QuoteExtension\Helper\Data $helperData
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param Url $catalogUrlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        QuoteVersion $quoteVersion,
        QuoteItemFactory $quoteItem,
        JsonHelper $jsonHelper,
        \Magento\Framework\App\State $state,
        QuoteVersionCollection $quoteVersionCollection,
        \Bss\QuoteExtension\Helper\Data $helperData,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        Url $catalogUrlBuilder
    ) {
        parent::__construct($context);
        $this->quoteVersion = $quoteVersion;
        $this->quoteItem = $quoteItem;
        $this->jsonHelper = $jsonHelper;
        $this->state = $state;
        $this->quoteVersionCollection = $quoteVersionCollection;
        $this->helperData = $helperData;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->catalogUrlBuilder = $catalogUrlBuilder;
    }

    /**
     * Set Data to quote version table
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param \Bss\QuoteExtension\Model\Quote $requestQuote
     * @throws \Exception
     */
    public function setDataToQuoteVersion($quote, $requestQuote)
    {
        $versionData = [];
        $items = $quote->getAllVisibleItems();

        foreach ($items as $item) {
            $dataItem = [];
            $itemId = $item->getId();
            if (!$item->getCustomPrice()) {
                $price  = [
                    'price'               => $item->getPrice(),
                    'base_price'          => $item->getBasePrice(),
                    'price_incl_tax'      => $item->getPriceInclTax(),
                    'base_price_incl_tax' => $item->getBasePriceInclTax()
                ];
            } else {
                $price  = [
                    'customprice'         => $item->getCustomPrice(),
                    'price'               => $item->getPrice(),
                    'base_price'          => $item->getBasePrice(),
                    'price_incl_tax'      => $item->getPriceInclTax(),
                    'base_price_incl_tax' => $item->getBasePriceInclTax()
                ];
            }
            $dataItem['price'] = $price;
            $dataItem['name'] = $item->getName();
            $dataItem['sku'] = $item->getSku();
            $dataItem['comment'] = $this->getQuoteItemComment($itemId);
            $dataItem['qty'] = $item->getQty();
            $versionData[$itemId] = $dataItem;
        }

        $data = [
            'quote_id' => $requestQuote->getId(),
            'version' => $requestQuote->getVersion() + 1,
            'status' => $requestQuote->getStatus(),
            'log' => $this->jsonHelper->serialize($versionData),
            'comment' => $quote->getLogComment(),
            'quote_id_not_comment' => $requestQuote->getQuoteIdNotComment(),
            'area_log' => $quote->getAreaLog()
        ];
        try {
            $this->quoteVersion->setData($data);
            $this->quoteVersion->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the quote version',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get history collection by request quote
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $requestQuote
     * @return QuoteVersionCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHistoryCollection($requestQuote)
    {
        $collection = $this->quoteVersionCollection->addFieldToFilter('quote_id', $requestQuote->getId());
        if (in_array($requestQuote->getStatus(), [Status::STATE_PENDING, Status::STATE_RESUBMIT])
            && $this->state->getAreaCode() != 'adminhtml'
        ) {
            $collection->addFieldToFilter(
                'quote_id_not_comment',
                [
                    ["null" => true],
                    ['neq' => (int)$requestQuote->getQuoteId()]
                ]
            );
        }

        if ($requestQuote->getStatus() == Status::STATE_UPDATED && $this->state->getAreaCode() == 'adminhtml') {
            $collection->addFieldToFilter(
                'quote_id_not_comment',
                [
                    ["null" => true],
                    ['neq' => (int)$requestQuote->getBackendQuoteId()]
                ]
            );
        }

        return $collection;
    }

    /**
     * Prepare html log
     *
     * @param string $log
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareLogHtml($log)
    {
        $logVersion = $this->jsonHelper->unserialize($log);
        $html = "";
        if (!empty($logVersion) && $logVersion) {
            foreach ($logVersion as $item) {
                $html .= '<div class="item-info">';
                $html .= '<p class="item-name"><strong>' . __($item['name']) . '</strong></p>';
                $html .= '<p class="item-qty">' . __('Qty: ') . $item['qty'] . '</p>';
                $html .= '<p class="item-price">' . __('Price: ') .
                    $this->helperData->formatPrice($item['price']['base_price']) . '</p>';
                $html .= '<p class="item-comment">' . __('Comment: ') . $item['comment'] . '</p>';
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Init Data Object
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    public function initDataObject($data)
    {
        $dataObject = $this->dataObjectFactory->create();
        $dataObject->setData($data);
        return $dataObject;
    }

    /**
     * Get Products
     *
     * @param array $products
     * @return mixed
     */
    public function getProducts($products)
    {
        return $this->catalogUrlBuilder->getRewriteByProductStore($products);
    }

    /**
     * Get Quote Item Comment
     *
     * @param int $itemId
     * @return mixed
     */
    public function getQuoteItemComment($itemId)
    {
        return $this->quoteItem->create()->load($itemId, 'item_id')->getComment();
    }

    /**
     * Get Comment
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $requestQuote
     * @param string $areaLog
     * @return array|null
     */
    public function getHistoryComment($requestQuote, $areaLog = null)
    {
        $collection = $this->quoteVersionCollection
            ->addFieldToFilter('quote_id', $requestQuote->getId());

        if ($areaLog == 'admin') {
            $collection = $this->quoteVersionCollection->addFieldToFilter('area_log', $areaLog);
            if (is_array($requestQuote->getOrigData()) && array_key_exists('quote_id', $requestQuote->getOrigData())){
                $collection->addFieldToFilter('quote_id_not_comment', $requestQuote->getOrigData()['quote_id']);
            }
        } elseif ($areaLog == 'customer') {
            $collection = $this->quoteVersionCollection->addFieldToFilter('area_log', ['null' => true]);
            if (is_array($requestQuote->getOrigData()) && array_key_exists('backend_quote_id', $requestQuote->getOrigData())){
                $collection->addFieldToFilter('quote_id_not_comment', $requestQuote->getOrigData()['backend_quote_id']);
            }
        }
        if ($collection) {
            $comments = [];
            foreach ($collection as $quote) {
                if ($quote->getComment()) {
                    $comments[] = $quote->getComment();
                }
            }
            return $comments;
        }
        return null;
    }
}
