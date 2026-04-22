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

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ExpiredQuote
 *
 * @package Bss\QuoteExtension\Helper\QuoteExtension
 */
class ExpiredQuote extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PATH_REQUEST4QUOTE_REMINDER = 'bss_request4quote/request4quote_global/reminder_day';
    const PATH_REQUEST4QUOTE_EXPIRED = 'bss_request4quote/request4quote_global/default_expire';

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
    /**
     * ExpiredQuote constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param TimezoneInterface $localeDate
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        TimezoneInterface $localeDate,
        QuoteFactory $quoteFactory
    ) {
        parent::__construct($context);
        $this->remoteAddress = $remoteAddress;
        $this->localeDate = $localeDate;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Get Reminder Days Config
     *
     * @param int $store
     * @return int
     */
    public function getReminderDays($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_REMINDER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Default Expired Days Config
     *
     * @param int $store
     * @return int
     */
    public function getDefaultExpiredDays($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_EXPIRED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Calculator Reminder Days
     *
     * @param string $daysCheck
     * @return bool|string
     */
    public function calculatorReminder($daysCheck)
    {
        $reminderDays = $this->getReminderDays();
        if ($reminderDays && $daysCheck) {
            $reminderDayCal = $this->localeDate->date(
                strtotime($daysCheck . ' - ' . $reminderDays . ' days'),
                null,
                false
            )->format('Y-m-d');
            return $reminderDayCal;
        }
        return false;
    }

    /**
     * Get Expired Days
     *
     * @param string $daysCheck
     * @return bool|string
     */
    public function getExpiredDay($daysCheck)
    {
        if ($daysCheck) {
            return $this->localeDate->date(
                strtotime($daysCheck),
                null,
                false
            )->format('Y-m-d');
        }
        return false;
    }

    /**
     * Calculator Expired Days
     *
     * @param string $days
     * @return bool|null
     */
    public function calculatorExpiredDay($days)
    {
        $expiredDaysConfig = $this->getDefaultExpiredDays();
        if ($expiredDaysConfig) {
            $expiredDays = (float) $expiredDaysConfig;
            $expiredDaysCal = $this->localeDate->date(
                strtotime($days . ' + ' . $expiredDays . ' days'),
                null,
                false
            )->format('Y-m-d H:i:s');
            return $expiredDaysCal;
        }
        return null;
    }

    /**
     * Get Request quote
     *
     * @param int $quoteId
     * @return \Magento\Quote\Model\Quote
     */
    public function getRequestQuote($quoteId)
    {
        return $this->quoteFactory->create()->loadByIdWithoutStore($quoteId);
    }

    /**
     * @return string
     */
    public function gretemoteAddress()
    {
        return $this->remoteAddress->getRemoteAddress();
    }
}
