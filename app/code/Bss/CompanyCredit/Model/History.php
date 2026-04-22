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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Model;

use Bss\CompanyCredit\Api\Data\HistoryInterface;
use Bss\CompanyCredit\Model\ResourceModel\History as ResourceModelHistory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class History.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class History extends AbstractModel implements HistoryInterface
{
    const TYPE_PLACE_ORDER = 1;
    const TYPE_ADMIN_REFUND = 2;
    const TYPE_ADMIN_CHANGES_CREDIT_LIMIT = 3;
    const TYPE_CHANGE_CREDIT_EXCESS_TO = 4;

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
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    private $timeZone;

    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $registry
     * @param SessionFactory $customerSession
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param TimezoneInterface $localeDate
     * @param FormatInterface $localeFormat
     * @param Timezone $timeZone
     * @param ProductMetadataInterface $productMetadata
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SessionFactory $customerSession,
        StoreManagerInterface $storeManager,
        DateTime $date,
        TimezoneInterface $localeDate,
        FormatInterface $localeFormat,
        Timezone $timeZone,
        ProductMetadataInterface $productMetadata
    ) {
        parent::__construct(
            $context,
            $registry
        );
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->date = $date;
        $this->localeDate = $localeDate;
        $this->localeFormat = $localeFormat;
        $this->timeZone = $timeZone;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModelHistory::class);
        $this->setIdFieldName('id');
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function setPONumber($pONumber)
    {
        return $this->setData(self::PO_NUMBER, $pONumber);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreditChange($creditChange)
    {
        return $this->setData(self::TYPE, $creditChange);
    }

    /**
     * {@inheritdoc}
     */
    public function setAvailableCreditCurrent($availableCreditCurrent)
    {
        return $this->setData(self::AVAILABLE_CREDIT_CURRENT, $availableCreditCurrent);
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowExceed($allowExceed)
    {
        return $this->setData(self::ALLOW_EXCEED, $allowExceed);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentStatus($paymentStatus)
    {
        return $this->setData(self::PAYMENT_STATUS, $paymentStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setUnpaidCredit($unpaidCredit)
    {
        return $this->setData(self::UNPAID_CREDIT, $unpaidCredit);
    }

    /**
     * {@inheritdoc}
     */
    public function getPONumber()
    {
        return $this->getData(self::PO_NUMBER);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditChange()
    {
        return $this->getData(self::CREDIT_CHANGE);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableCreditCurrent()
    {
        return $this->getData(self::AVAILABLE_CREDIT_CURRENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowExceed()
    {
        return $this->getData(self::ALLOW_EXCEED);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentStatus()
    {
        return $this->getData(self::PAYMENT_STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnpaidCredit()
    {
        return $this->getData(self::UNPAID_CREDIT);
    }

    /**
     * Load history by customer
     *
     * @param int|null $customerId
     * @param int|null $websiteId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadByCustomer($customerId = null)
    {
        if ($customerId === null) {
            $customerId = (int) $this->customerSession->create()->getCustomer()->getId();
        }
        return $this->getCollection()
            ->addFieldToFilter(
                'customer_id',
                $customerId
            )->setOrder(
                'id',
                'DESC'
            );
    }

    /**
     * Update History
     *
     * @param array $data
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateHistory($data)
    {
        try {
            $this->setData($data);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}
