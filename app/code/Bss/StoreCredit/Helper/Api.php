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
namespace Bss\StoreCredit\Helper;

use Bss\StoreCredit\Api\Data\HistoryInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 */
class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Current locale
     *
     * @var string
     */
    protected $locale;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var DateTime
     */
    protected $datetime;

    /**
     * Data constructor.
     *
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     * @param Context $context
     */
    public function __construct(
        DateTime $dateTime,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver,
        Context $context
    ) {
        $this->datetime = $dateTime;
        $this->timezone = $timezone;
        $this->locale = $localeResolver->getLocale();
        parent::__construct($context);
    }

    /**
     * Get time from to
     *
     * @param string $fromTo
     * @return string|null
     */
    public function getFromTo($fromTo){
        if($fromTo == null){
            return null;
        }
        $fromTo = $this->convertDate(
            $fromTo,
            0,
            0,
            0
        );
        return $fromTo->format('Y-m-d H:i:s');
    }

    /**
     * Get time to date
     *
     * @param $toDate
     * @return string|null
     */
    public function getToDate($toDate){
        if($toDate == null){
            return null;
        }
        $toDate = $this->convertDate(
            $toDate,
            23,
            59,
            59
        );
        return $toDate->format('Y-m-d H:i:s');
    }

    /**
     * Convert Date
     *
     * @param string $date
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param bool $setUtcTimeZone
     * @return \DateTime|null
     */
    public function convertDate($date, $hour = 0, $minute = 0, $second = 0, $setUtcTimeZone = true)
    {
        try {
            $dateObj = $this->timezone->date($date, $this->getLocale(), true);
            $dateObj->setTime($hour, $minute, $second);
            //convert store date to default date in UTC timezone without DST
            if ($setUtcTimeZone) {
                $dateObj->setTimezone(new \DateTimeZone('UTC'));
            }
            return $dateObj;
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
            return null;
        }
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get Output time
     *
     * @param string $time
     * @return string|null
     */
    public function getOutputTime($time)
    {
        try {
            $date = $this->timezone->date(new \DateTime($time));
            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
            return null;
        }
    }

    /**
     * Convert created_time and updated_time same display in magento
     *
     * @param \Bss\StoreCredit\Api\HistoryCreditSearchResultsInterface $history
     * @return HistoryInterface[]|ExtensibleDataInterface[]
     */
    public function getHistoryItemFormatDate($history) {
        foreach ($history->getItems() as $item) {
            $item->setCreatedTime($this->getOutputTime($item->getCreatedTime()));
            $item->setUpdatedTime($this->getOutputTime($item->getUpdatedTime()));
        }
        return $history->getItems();
    }

}
