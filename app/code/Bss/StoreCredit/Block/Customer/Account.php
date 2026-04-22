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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Block\Customer;

use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Bss\StoreCredit\Helper\Data as StoreCreditData;
use Bss\StoreCredit\Model\Currency;
use Bss\StoreCredit\Model\History;
use Bss\StoreCredit\Model\HistoryFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Magento\Customer\Model\Session;

/**
 * Class Account
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Account extends Template
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    private $storeCreditRepository;

    /**
     * @var \Bss\StoreCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Construct
     *
     * @param Currency $currency
     * @param Context $context
     * @param HistoryFactory $historyFactory
     * @param StoreCreditData $bssStoreCreditHelper
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param ResourceConnection $resource
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        Context $context,
        HistoryFactory $historyFactory,
        StoreCreditData $bssStoreCreditHelper,
        StoreCreditRepositoryInterface $storeCreditRepository,
        ResourceConnection $resource,
        Session $customerSession,
        array $data = []
    ) {
        $this->currency = $currency;
        parent::__construct($context, $data);
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->historyFactory = $historyFactory;
        $this->localeDate = $context->getLocaleDate();
        $this->storeCreditRepository = $storeCreditRepository;
        $this->customerSession = $customerSession;
        $this->resource = $resource;
    }

    /**
     * Prepare the layout of the history block.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getHistory()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'bss.storecredit.history.pager'
            )->setAvailableLimit(
                [
                    10 => 10,
                    15 => 15,
                    20 => 20
                ]
            )->setShowPerPage(
                true
            )->setCollection(
                $this->getHistory()
            );
            $this->setChild('pager', $pager);
            $this->getHistory()->load();
        }
        return $this;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get balance given the customer Id stored in the session.
     *
     * @return string
     */
    public function getBalanceWebsite()
    {
        $credit = $this->storeCreditRepository->get();
        $amount = 0;
        if (!empty($credit->getData())) {
            $amount = $credit->getBalanceAmount();
        }
        $currencyCode = $this->currency->getCreditCurrencyCode($credit->getCurrencyCode());
        return $this->currency->displayStoreView($amount, $currencyCode);
    }

    /**
     * Return the History given the customer Id stored in the session.
     *
     * @return \Bss\StoreCredit\Model\History
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHistory()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;
        $collection = $this->historyFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(
            [
                'sales_order_table' => $this->resource->getConnection()->getTableName('sales_order')
            ],
            'main_table.order_id = sales_order_table.entity_id',
            [
                'order_increment_id' => 'sales_order_table.increment_id'
            ]
        )->joinLeft(
            [
                'creditmemo_table' => $this->resource->getConnection()->getTableName('sales_creditmemo')
            ],
            'main_table.creditmemo_id = creditmemo_table.entity_id',
            [
                'creditmemo_increment_id' => 'creditmemo_table.increment_id'
            ]
        );
        $collection->addFieldToFilter(
            'main_table.customer_id',
            $this->customerSession->getCustomerId()
        );
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->setOrder('main_table.history_id', 'DESC');
        return $collection;
    }

    /**
     * Convert price with currency
     *
     * @param float $price
     * @param null|string $currencyCode
     * @return  string
     */
    public function convertPriceBalance($price, $currencyCode = null)
    {
        return $this->currency->displayStoreView($price, $currencyCode);
    }

    /**
     * Convert price change amount: Compatible with old database
     *
     * @param \Bss\StoreCredit\Model\History $history
     */
    public function convertPriceChangeAmount($history)
    {
        if ($amount = $history->getChangeAmountStoreView()) {
            return $this->currency->displayStoreView($amount, $history->getCurrencyCode());
        }
        $amount = $history->getChangeAmount();
        return $this->currency->displayStoreView($amount, $this->currency->getCurrencyCodeByWebsite());
    }

    /**
     * Get type action by value
     *
     * @param   int $value
     * @return  string
     */
    public function getTypeAction($value)
    {
        return $this->bssStoreCreditHelper->getTypeAction($value);
    }

    /**
     * Convert update time
     *
     * @param   string $time
     * @return  string
     */
    public function formatDateTime($time)
    {
        return $this->localeDate->formatDateTime($time, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);
    }

    /**
     * Load additional info of history by customer
     *
     * @param History $history
     * @param int|null $historyType
     * @return string
     */
    public function getAddition($history, $historyType = null)
    {
        if ($historyType != null) {
            if ($history) {
                $value = '<span>';
                switch ($historyType) {
                    case History::TYPE_CANCEL:
                        if ($history->getOrderIncrementId()) {
                            $url = $this->getUrl(
                                'sales/order/view',
                                ['order_id' => $history->getOrderId()]
                            );
                            $value .= '<a href="' . $url . '"">';
                            $value .= __('Order # %1 is canceled', $history->getOrderIncrementId());
                            $value .= '</a>';
                        } else {
                            $value .= __('Order is Deleted');
                        }
                        break;
                    case History::TYPE_UPDATE:
                        $value .= $history->getCommentContent();
                        break;
                    case History::TYPE_USED_IN_ORDER:
                        if ($history->getOrderIncrementId()) {
                            $url = $this->getUrl(
                                'sales/order/view',
                                ['order_id' => $history->getOrderId()]
                            );
                            $value .= '<a href="' . $url . '"">';
                            $value .= __('Order # %1', $history->getOrderIncrementId());
                            $value .= '</a>';
                        } else {
                            $value .= __('Order is Deleted');
                        }
                        break;
                    case History::TYPE_REFUND:
                        if ($history->getCreditmemoIncrementId()) {
                            $url = $this->getUrl(
                                'sales/order/creditmemo',
                                ['order_id' => $history->getOrderId()]
                            );
                            $value .= '<a href="' . $url . '"">';
                            $value .= __('Credit Memo # %1', $history->getCreditmemoIncrementId());
                            $value .= '</a>';
                        } else {
                            $value .= __('Credit Memo is Deleted');
                        }
                        break;
                    default:
                        break;
                }
                $value .= '</span>';
                return $value;
            }
        }
        return null;
    }
}
