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
namespace Bss\CompanyCredit\Block\Customer\Account;

use Bss\CompanyCredit\Helper\Currency as HelperCurrency;
use Bss\CompanyCredit\Helper\Data as CompanyCreditData;
use Bss\CompanyCredit\Model\History;
use Bss\CompanyCredit\Model\HistoryFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Theme\Block\Html\Pager;

class LogTransaction extends Template
{
    /**
     * @var HelperCurrency
     */
    protected $helperCurrency;

    /**
     * @var \Bss\CompanyCredit\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Bss\CompanyCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \Bss\CompanyCredit\Model\UpdatePaymentStatus
     */
    protected $updatePaymentStatus;

    /**
     * LogTransaction Construct
     *
     * @param HelperCurrency $helperCurrency
     * @param Context $context
     * @param HistoryFactory $historyFactory
     * @param CompanyCreditData $helperData
     * @param OrderRepositoryInterface $orderRepository
     * @param \Bss\CompanyCredit\Model\UpdatePaymentStatus $updatePaymentStatus
     * @param array $data
     */
    public function __construct(
        HelperCurrency $helperCurrency,
        Context $context,
        HistoryFactory $historyFactory,
        CompanyCreditData $helperData,
        OrderRepositoryInterface $orderRepository,
        \Bss\CompanyCredit\Model\UpdatePaymentStatus $updatePaymentStatus,
        array $data = []
    ) {
        $this->helperCurrency = $helperCurrency;
        parent::__construct($context, $data);
        $this->helperData = $helperData;
        $this->historyFactory = $historyFactory;
        $this->localeDate = $context->getLocaleDate();
        $this->orderRepository = $orderRepository;
        $this->updatePaymentStatus = $updatePaymentStatus;
    }

    /**
     * Prepare the layout of the history block.
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getHistories()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'bss.companycredit.history.pager'
            )->setAvailableLimit(
                [
                    10 => 10,
                    15 => 15,
                    20 => 20
                ]
            )->setShowPerPage(
                true
            )->setCollection(
                $this->getHistories()
            );
            $this->setChild('pager', $pager);
            $this->getHistories()->load();
        }
        return $this;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     * @throws LocalizedException
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Return the History given the customer Id stored in the session.
     *
     * @return AbstractCollection
     * @throws NoSuchEntityException
     */
    public function getHistories()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;
        $collection = $this->historyFactory->create()->loadByCustomer();
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    /**
     * Convert price with currency
     *
     * @param float $price
     * @param string $currencyCode
     * @return float|string
     */
    public function convertPrice($price, $currencyCode)
    {
        $currencyCodeWebsite = $this->helperCurrency->getCurrencyCodeByWebsite();
        $price = $this->helperCurrency->convertCurrency($price, $currencyCode, $currencyCodeWebsite);
        return $this->helperCurrency->currency($price, true, false);
    }

    /**
     * Get type action by value
     *
     * @param int $value
     * @param string $allowExceed
     * @return \Magento\Framework\Phrase|string
     */
    public function getTypeAction($value, $allowExceed)
    {
        return $this->helperData->getTypeAction($value, $allowExceed);
    }

    /**
     * Convert time
     *
     * @param string|null $time
     * @return string
     */
    public function formatDateTime($time)
    {
        if (!$time) {
            return '';
        }

        return $this->localeDate->formatDateTime($time, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);
    }

    /**
     * Convert date
     *
     * @param string|null $time
     * @return string
     */
    public function formatDateToString($time)
    {
        if (!$time) {
            return '';
        }

        return $this->localeDate->formatDate($time, \IntlDateFormatter::MEDIUM);
    }

    /**
     * Get url order
     *
     * @param int $orderId
     * @param int $type
     * @return string
     */
    public function viewOrder($orderId, $type)
    {
        if ($orderId) {
            $value = '<span>';
            switch ($type) {
                case History::TYPE_PLACE_ORDER:
                    try {
                        $order = $this->orderRepository->get($orderId);
                        $url = $this->getUrl(
                            'sales/order/view',
                            ['order_id' => $orderId]
                        );
                        $value .= '<a href="' . $url . '"">';
                        $value .= __('Order # %1', $order->getIncrementId());
                        $value .= '</a>';
                    } catch (\Exception $exception) {
                        $this->_logger->critical($exception->getMessage());
                    }
                    break;
                default:
                    break;
            }
            $value .= '</span>';
            return $value;
        }
        return null;
    }

    /**
     * Show payment status.
     *
     * @param string|null $paymentStatus
     * @param string|null $paymentDueDate
     * @return string|null
     */
    public function showPaymentStatus($paymentStatus, $paymentDueDate)
    {
        return $this->updatePaymentStatus->showPaymentStatus($paymentStatus, $paymentDueDate);
    }
}
