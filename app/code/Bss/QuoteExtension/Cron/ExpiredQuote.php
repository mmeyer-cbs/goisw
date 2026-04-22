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
namespace Bss\QuoteExtension\Cron;

use Bss\QuoteExtension\Helper\Data;
use Bss\QuoteExtension\Helper\Mail as HelperMail;
use Bss\QuoteExtension\Model\Config\Source\ExpiryMailStatus;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory as ManageQuoteFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class ExpiredQuote
 *
 * @package Bss\QuoteExtension\Cron
 */
class ExpiredQuote
{
    protected $errors = [];

    /**
     * @var ManageQuoteFactory
     */
    protected $manageQuoteFactory;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var HelperMail
     */
    protected $helperEmail;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote
     */
    protected $helperExpired;

    /**
     * ExpiredQuote constructor.
     * @param ManageQuoteFactory $manageQuoteFactory
     * @param TimezoneInterface $localeDate
     * @param Data $helperData
     * @param HelperMail $helperEmail
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote $helperExpired
     */
    public function __construct(
        ManageQuoteFactory $manageQuoteFactory,
        TimezoneInterface $localeDate,
        Data $helperData,
        HelperMail $helperEmail,
        \Psr\Log\LoggerInterface $logger,
        \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote $helperExpired
    ) {
        $this->manageQuoteFactory = $manageQuoteFactory;
        $this->localeDate = $localeDate;
        $this->helperData = $helperData;
        $this->helperEmail = $helperEmail;
        $this->logger = $logger;
        $this->helperExpired = $helperExpired;
    }

    /**
     * Cron Check execute
     *
     * @return $this
     */
    public function execute()
    {
        $enable = $this->helperData->isEnable();
        if ($enable) {
            try {
                $manageQuoteCollection = $this->manageQuoteFactory->create();
                if ($manageQuoteCollection->getSize() == 0) {
                    return $this;
                }
                foreach ($manageQuoteCollection as $requestQuote) {
                    $dayCheck = $requestQuote->getExpiry();
                    $currentDate = $this->helperData->getCurrentDate();
                    $status = $requestQuote->getStatus();
                    $quoteId = $requestQuote->getQuoteId();
                    $quote = $this->helperExpired->getRequestQuote($quoteId);
                    $checkStatus = $this->checkStatus($status);
                    if ($checkStatus && $quote->getEntityId()) {
                        $reminderDay = $this->helperExpired->calculatorReminder($dayCheck);
                        $expiredDay = $this->helperExpired->getExpiredDay($dayCheck);
                        $this->checkReminderDays($reminderDay, $currentDate, $quote, $requestQuote);
                        $this->checkExpiredDays($expiredDay, $currentDate, $quote, $requestQuote);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        return $this;
    }

    /**
     * Check reminder days and send notification email
     *
     * @param string $reminderDay
     * @param string $currentDate
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Bss\QuoteExtension\Model\ManageQuote $requestQuote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function checkReminderDays($reminderDay, $currentDate, $quote, $requestQuote)
    {
        if ($requestQuote->getData('expiry_email_sent') == ExpiryMailStatus::STATUS_NOT_SENT) {
            if ($reminderDay && (strtotime($currentDate) >= strtotime($reminderDay))) {
                try {
                    $requestQuote->setData('expiry_email_sent', ExpiryMailStatus::STATUS_SENT);
                    $requestQuote->setNotSendEmail(true)->save();
                    $this->helperEmail->sendNotificationQuoteReminderEmail($quote, $requestQuote);
                } catch (\Exception $exception) {
                    throw new CouldNotSaveException(__(
                        'Could not save the quote',
                        $exception->getMessage()
                    ));
                }
            }
        }
    }

    /**
     * Check expired days and set expired status
     *
     * @param string $expiredDay
     * @param string $currentDate
     * @param \Bss\QuoteExtension\Model\ManageQuote $requestQuote
     * @throws CouldNotSaveException
     */
    protected function checkExpiredDays($expiredDay, $currentDate, $quote, $requestQuote)
    {
        if ($expiredDay && (strtotime($currentDate) >= strtotime($expiredDay))) {
            try {
                $requestQuote->setStatus(Status::STATE_EXPIRED);
                $requestQuote->save();
                $this->helperEmail->sendNotificationExpiredEmail($quote, $requestQuote);
            } catch (\Exception $exception) {
                throw new CouldNotSaveException(__(
                    'Could not save the quote',
                    $exception->getMessage()
                ));
            }
        }
    }

    /**
     * Status Quote allow to check
     *
     * @param string $status
     * @return bool
     */
    private function checkStatus($status)
    {
        $ingnoreStatus = [Status::STATE_EXPIRED, Status::STATE_ORDERED, Status::STATE_CANCELED];
        if (in_array($status, $ingnoreStatus)) {
            return false;
        }
        return true;
    }
}
