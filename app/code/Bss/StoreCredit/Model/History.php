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
namespace Bss\StoreCredit\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Bss\StoreCredit\Model\ResourceModel\History as ResourceModelHistory;
use Bss\StoreCredit\Api\Data\HistoryInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class History
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class History extends AbstractModel implements HistoryInterface
{
    const TYPE_CANCEL = 4;

    const TYPE_UPDATE = 3;

    const TYPE_USED_IN_ORDER = 2;

    const TYPE_REFUND = 1;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Bss\StoreCredit\Model\Email
     */
    private $email;

    /**
     * Construct
     *
     * @param Currency $currency
     * @param Context $context
     * @param Registry $registry
     * @param SessionFactory $customerSession
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param Email $email
     * @param ProductMetadataInterface $productMetadata
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        Context $context,
        Registry $registry,
        SessionFactory $customerSession,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        Email $email,
        ProductMetadataInterface $productMetadata
    ) {
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->localeDate = $localeDate;
        $this->email = $email;
        $this->productMetadata = $productMetadata;
        parent::__construct(
            $context,
            $registry
        );
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModelHistory::class);
        $this->setIdFieldName('history_id');
    }

    /**
     * @param int $customerId
     * @return HistoryInterface|History
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @param int $creditmemoId
     * @return HistoryInterface|History
     */
    public function setCreditmemoId($creditmemoId)
    {
        return $this->setData(self::CREDITMEMO_ID, $creditmemoId);
    }

    /**
     * @param int $orderId
     * @return HistoryInterface|History
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @param int $websiteId
     * @return HistoryInterface|History
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @param string $type
     * @return HistoryInterface|History
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @param float $amount
     * @return HistoryInterface|History
     */
    public function setChangeAmount($amount)
    {
        return $this->setData(self::CHANGE_AMOUNT, $amount);
    }

    /**
     * @param float $amount
     * @return HistoryInterface|History
     */
    public function setBalanceAmount($amount)
    {
        return $this->setData(self::BALANCE_AMOUNT, $amount);
    }

    /**
     * @param string $comment
     * @return HistoryInterface|History
     */
    public function setCommentContent($comment)
    {
        return $this->setData(self::COMMENT_CONTENT, $comment);
    }

    /**
     * @param bool $isNotified
     * @return HistoryInterface|History
     */
    public function setIsNotified($isNotified)
    {
        return $this->setData(self::IS_NOTIFIED, $isNotified);
    }

    /**
     * @return int|mixed
     */
    public function getHistoryId()
    {
        return $this->getData(self::HISTORY_ID);
    }

    /**
     * @return int|mixed
     */
    public function getCreditmemoId()
    {
        return $this->getData(self::CREDITMEMO_ID);
    }

    /**
     * @return int|mixed
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @return mixed|string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @return float|mixed
     */
    public function getChangeAmount()
    {
        return $this->getData(self::CHANGE_AMOUNT);
    }

    /**
     * @return float|mixed
     */
    public function getBalanceAmount()
    {
        return $this->getData(self::BALANCE_AMOUNT);
    }

    /**
     * @return mixed|string
     */
    public function getCommentContent()
    {
        return $this->getData(self::COMMENT_CONTENT);
    }

    /**
     * @return string
     */
    public function getCreatedTime()
    {
        return $this->getData(self::CREATED_TIME);
    }

    /**
     * @return string
     */
    public function getUpdatedTime()
    {
        return $this->getData(self::UPDATED_TIME);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * @inheridoc
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /**
     * @inheridoc
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * @inheridoc
     */
    public function setCreditCurrencyCode($creditCurrencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $creditCurrencyCode);
    }

    /**
     * @inheridoc
     */
    public function getCreditCurrencyCode()
    {
        return $this->getData(self::CREDIT_CURRENCY_CODE);
    }

    /**
     * @inheridoc
     */
    public function setChangeAmountStoreView($amount)
    {
        return $this->setData(self::CHANGE_AMOUNT_STORE_VIEW, $amount);
    }

    /**
     * @inheridoc
     */
    public function getChangeAmountStoreView()
    {
        return $this->getData(self::CHANGE_AMOUNT_STORE_VIEW);
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return [
            self::TYPE_CANCEL => __('Revert'),
            self::TYPE_UPDATE => __('Update'),
            self::TYPE_USED_IN_ORDER => __('Used in order'),
            self::TYPE_REFUND => __('Refund')
        ];
    }

    /**
     * Load history by customer
     *
     * @param int|null $customerId
     * @param int|null $websiteId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadByCustomer($customerId = null, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = (int) $this->storeManager->getStore()->getWebsiteId();
        }
        if ($customerId === null) {
            $customerId = (int) $this->customerSession->create()->getCustomer()->getId();
        }
        $history = $this->getCollection()
            ->addFieldToFilter(
                'customer_id',
                $customerId
            )->addFieldToFilter(
                'main_table.website_id',
                $websiteId
            )->setOrder(
                'history_id',
                'DESC'
            );
        return $history;
    }

    /**
     * Load history by order
     *
     * @param int $orderId
     * @param int $websiteId
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadByOrder($orderId, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = (int) $this->storeManager->getStore()->getWebsiteId();
        }
        $history = $this->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )->addFieldToFilter(
                'main_table.website_id',
                $websiteId
            )->addFieldToFilter(
                'main_table.type',
                2
            )->getFirstItem();
        return $history;
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     * @param string $period
     * @param int|null $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadReportData($dateStart, $dateEnd, $period, $websiteId = null)
    {
        $reports = [];
        $balanceUp = [];
        $balanceDown = [];
        $dateStart = $this->localeDate->date(strtotime($dateStart), null, false);
        $dateEnd = $this->localeDate->date(strtotime($dateEnd), null, false);
        $intervals = $this->_getIntervals($dateStart, $dateEnd, $period);
        foreach ($intervals as $interval) {
            $reports['period'][] = $interval['period'];
            $balanceUp[] = $this->_getReport($interval, $period, true, $websiteId);
            $balanceDown[] = $this->_getReport($interval, $period, null, $websiteId);
        }
        $reports['amount'] = [
            [
                'name' => __('Balance Up'),
                'data' => $balanceUp
            ],[
                'name' => __('Balance Down'),
                'data' => $balanceDown
            ]
        ];
        $reports['priceFormat'] = $this->currency->getPriceFormat($websiteId);
        return $reports;
    }

    /**
     * Get interval for a day
     *
     * @param \DateTime $dateStart
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getDayInterval(\DateTime $dateStart)
    {
        $interval = [
            'period' => $this->localeDate->formatDateTime(
                $this->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00')),
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE
            ),
            'start' => $this->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00')),
            'end' => $this->convertConfigTimeToUtc($dateStart->format('Y-m-d 23:59:59')),
        ];
        return $interval;
    }

    /**
     * Get intervals
     *
     * @param \DateTime $dateStart
     * @param \DateTime $dateEnd
     * @param string $period
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getIntervals($dateStart, $dateEnd, $period)
    {
        $firstInterval = true;
        $intervals = [];
        while ($dateStart <= $dateEnd) {
            switch ($period) {
                case 'day':
                    $interval = $this->_getDayInterval($dateStart);
                    $dateStart->modify('+1 day');
                    break;
                case 'month':
                    $interval = $this->_getMonthInterval($dateStart, $dateEnd, $firstInterval);
                    $firstInterval = false;
                    break;
                case 'year':
                    $interval = $this->_getYearInterval($dateStart, $dateEnd, $firstInterval);
                    $firstInterval = false;
                    break;
                default:
                    break;
            }
            if (isset($interval['period'])) {
                $intervals[$interval['period']] = $interval;
            }
        }
        return $intervals;
    }

    /**
     * Get Interval for a year
     *
     * @param \DateTime $dateStart
     * @param \DateTime $dateEnd
     * @param bool $firstInterval
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getYearInterval(\DateTime $dateStart, \DateTime $dateEnd, $firstInterval)
    {
        $interval = [];
        $interval['period'] = $dateStart->format('Y');
        $interval['start'] = $firstInterval
            ? $this->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00'))
            : $this->convertConfigTimeToUtc($dateStart->format('Y-01-01 00:00:00'));

        $interval['end'] = $dateStart->diff($dateEnd)->y == 0
            ? $this->convertConfigTimeToUtc(
                $dateStart->setDate($dateStart->format('Y'), $dateEnd->format('m'), $dateEnd->format('d'))
                    ->format('Y-m-d 23:59:59')
            )
            : $this->convertConfigTimeToUtc($dateStart->format('Y-12-31 23:59:59'));
        $dateStart->modify('+1 year');

        if ($dateStart->diff($dateEnd)->y == 0) {
            $dateStart->setDate($dateStart->format('Y'), 1, 1);
        }

        return $interval;
    }

    /**
     * Get interval for a month
     *
     * @param \DateTime $dateStart
     * @param \DateTime $dateEnd
     * @param bool $firstInterval
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getMonthInterval(\DateTime $dateStart, \DateTime $dateEnd, $firstInterval)
    {
        $interval = [];
        $interval['period'] = $dateStart->format('m/Y');
        if ($firstInterval) {
            $interval['start'] = $this->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00'));
        } else {
            $interval['start'] = $this->convertConfigTimeToUtc($dateStart->format('Y-m-01 00:00:00'));
        }

        if ($dateStart->diff($dateEnd)->m == 0) {
            $interval['end'] = $this->convertConfigTimeToUtc(
                $dateStart->setDate(
                    $dateStart->format('Y'),
                    $dateStart->format('m'),
                    $dateEnd->format('d')
                )->format(
                    'Y-m-d 23:59:59'
                )
            );
        } else {
            $interval['end'] = $this->convertConfigTimeToUtc(
                $dateStart->format('Y-m-' . date('t', $dateStart->getTimestamp()) . ' 23:59:59')
            );
        }

        $dateStart->modify('+1 month');

        if ($dateStart->diff($dateEnd)->m == 0) {
            $dateStart->setDate($dateStart->format('Y'), $dateStart->format('m'), 1);
        }

        return $interval;
    }

    /**
     * Get report for some interval
     *
     * @param array $interval
     * @param string $period
     * @param bool|null $refund
     * @param int|null $webisteId
     * @return array
     */
    private function _getReport($interval, $period, $refund = null, $webisteId = null)
    {
        return $this->getCollection()->getReport($interval, $period, $refund, $webisteId);
    }

    /**
     * Update History
     *
     * @param array $data
     * @param int|null $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateHistory($data, $storeId = null, $dataCustomer = null)
    {
        try {
            $this->setData($data)->save();
            if ($this->getIsNotified()) {
                $this->email->sendMailNotify($storeId, $this->getCustomerId(), $this, $this->getCommentContent(), $dataCustomer);
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param string|\DateTimeInterface $dateStartFomat
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function convertConfigTimeToUtc($dateStartFomat)
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.1.0') >= 0) {
            return $this->localeDate->convertConfigTimeToUtc($dateStartFomat);
        }
        return $dateStartFomat;
    }
}
