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

use Bss\CompanyCredit\Api\RemindRepositoryInterface as RemindRepository;
use Bss\CompanyCredit\Model\ResourceModel\Remind\CollectionFactory as RemindCollection;

class UpdateRemind
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
     * @var RemindFactory
     */
    protected $remindFactory;

    /**
     * @var UpdatePaymentStatus
     */
    protected $paymentStatus;

    /**
     * Construct.
     *
     * @param RemindCollection $remindCollection
     * @param RemindRepository $remindRepository
     * @param RemindFactory $remindFactory
     * @param \Bss\CompanyCredit\Model\UpdatePaymentStatus $paymentStatus
     */
    public function __construct(
        RemindCollection $remindCollection,
        RemindRepository $remindRepository,
        RemindFactory $remindFactory,
        UpdatePaymentStatus $paymentStatus
    ) {
        $this->remindCollection = $remindCollection;
        $this->remindRepository = $remindRepository;
        $this->remindFactory = $remindFactory;
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * Update multiple remind.
     *
     * @param int|null $dayMailBeforeOverdue
     */
    public function updateMultiple($dayMailBeforeOverdue)
    {
        $collection = $this->remindCollection->create();
        $remindItems = $collection->getItems();

        foreach ($remindItems as $remind) {
            $daySendMail = $this->paymentStatus->getDaySendMail($remind['payment_due_date'], $dayMailBeforeOverdue);
            $remind->setSendingTime($daySendMail);
            $this->remindRepository->save($remind);
        }
    }
}
