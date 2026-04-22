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
namespace Bss\CompanyCredit\Cron;

use Bss\CompanyCredit\Api\RemindRepositoryInterface as RemindRepository;
use Bss\CompanyCredit\Helper\Email;
use Bss\CompanyCredit\Model\ResourceModel\Remind\CollectionFactory as RemindCollection;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SendEmail
{
    /**
     * @var RemindCollection
     */
    protected $remindCollection;

    /**
     * @var RemindRepository
     */
    protected $remindRepository;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Bss\CompanyCredit\Block\Customer\Account\LogTransaction
     */
    protected $logTransaction;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Construct.
     *
     * @param RemindCollection $remindCollection
     * @param RemindRepository $remindRepository
     * @param DateTimeFactory $dateFactory
     * @param Email $emailHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Bss\CompanyCredit\Block\Customer\Account\LogTransaction $logTransaction
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        RemindCollection $remindCollection,
        RemindRepository $remindRepository,
        DateTimeFactory $dateFactory,
        Email $emailHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Bss\CompanyCredit\Block\Customer\Account\LogTransaction $logTransaction,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->remindCollection = $remindCollection;
        $this->remindRepository = $remindRepository;
        $this->dateFactory = $dateFactory;
        $this->emailHelper = $emailHelper;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->logTransaction = $logTransaction;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Send mail by cron.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $dateFactory = $this->dateFactory->create();
        $toTime = $dateFactory->gmtDate();
        $fromDateStr = strtotime('-1 hour', strtotime($toTime));
        $fromTime = $dateFactory->date('Y-m-d H:i:s', $fromDateStr);

        $this->remindSendMail('sending_time', $fromTime, $toTime);
        $this->remindSendMail('payment_due_date', $fromTime, $toTime);
    }

    /**
     * Get email prepare sending.
     *
     * @param string $field
     * @param string $fromTime
     * @param string $toTime
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function remindSendMail($field, $fromTime, $toTime)
    {
        $collection = $this->remindCollection->create()
            ->addFieldToFilter(
                $field,
                ['from' => $fromTime]
            )
            ->addFieldToFilter(
                $field,
                ['to' => $toTime]
            );

        $remindItems = $collection->getItems();

        if ($remindItems) {
            $this->send($remindItems, $field);
        }
    }

    /**
     * Send email.
     *
     * @param array $remindItems
     * @param string $field
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function send($remindItems, $field)
    {
        foreach ($remindItems as $remind) {
            try {
                $customer = $this->customerRepositoryInterface->getById($remind->getCustomerId());
            } catch (\Exception $e) {
                $customer = null;
            }

            if ($customer) {
                $customerName = $customer->getFirstname() . " " . $customer->getLastname();
                $dataEmail = [
                    "store_id" => $customer->getStoreId(),
                    "website_id" => $customer->getWebsiteId(),
                    "variables" => [
                        "customer_name" => $customerName,
                        "order_id" => $remind->getOrderId(),
                        "po_number" => $remind->getPoNumber(),
                        "payment_due_date" => $this->logTransaction->formatDateToString($remind->getPaymentDueDate())
                    ]
                ];

                if ($field == 'sending_time') {
                    $dataEmail['customer_email'] = $customer->getEmail();
                    $dataEmail['variables']['x_day'] = 1;
                    $this->emailHelper->sendEmailAdmin($dataEmail, 'sendDueDatePaymentReminder');
                }

                if ($field == 'payment_due_date') {
                    $dataEmail['customer_email'] = $this->emailHelper->getEmailReceiveEmail();
                    $this->emailHelper->sendEmailAdmin($dataEmail, 'sendNotificationOverdue');
                }

                $sent = $remind->getSent() !== null ? $remind->getSent() : 0;
                $sent++;
                $remind->setSent($sent);
                $this->remindRepository->save($remind);
            }
        }
    }
}
