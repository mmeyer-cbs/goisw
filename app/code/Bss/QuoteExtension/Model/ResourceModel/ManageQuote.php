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
namespace Bss\QuoteExtension\Model\ResourceModel;

use Bss\QuoteExtension\Model\Config\Source\Status;

/**
 * Class ManageQuote
 *
 * @package Bss\QuoteExtension\Model\ResourceModel
 */
class ManageQuote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Bss\QuoteExtension\Helper\Mail
     */
    protected $mailHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Bss\QuoteExtension\Helper\Mail $mailHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\App\State $state
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Bss\QuoteExtension\Helper\Mail $mailHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\State $state,
                                                          $connectionName = null
    ) {
        $this->mailHelper = $mailHelper;
        $this->quoteRepository = $quoteRepository;
        $this->state = $state;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_extension', 'entity_id');
    }

    /**
     * After Save Manage Quote
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $quote = $this->quoteRepository->get($object->getQuoteId());

        if ($quote) {
            if ($object->getIsAdminSubmitted()) {
                $quote->setIsAdminSubmitted(true);
            }
        }
        if (!$object->getNotSendEmail()) {
            switch ($object->getStatus()) {
                case Status::STATE_REJECTED:
                    $this->mailHelper->sendNotificationQuoteRejectedEmail($quote, $object);
                    break;
                case Status::STATE_PENDING:
                    if ($this->state->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMINHTML) {
                        $this->mailHelper->sendNotificationNewQuoteEmail($quote, $object);
                        $this->mailHelper->sendNotificationNewQuoteEmailForCustomer($quote, $object);
                    }
                    break;
                case Status::STATE_CANCELED:
                    break;
                case Status::STATE_EXPIRED:
                    $this->mailHelper->sendNotificationExpiredEmail($quote, $object);
                    break;
                case Status::STATE_ORDERED:
                    $this->mailHelper->sendNotificationQuoteOrderedEmail($quote, $object);
                    break;
                case Status::STATE_UPDATED:
                    if ($this->state->getAreaCode() !== \Magento\Framework\App\Area::AREA_FRONTEND) {
                        $this->mailHelper->sendNotificationAcceptQuoteEmail($quote, $object);
                        break;
                    }
                    break;
                case Status::STATE_COMPLETE:
                    if (!$object->getEmailSent()) {
                        $this->mailHelper->sendNotificationCompleteQuoteEmail($quote, $object);
                    }
                    break;
            }
        }
        return parent::_afterSave($object);
    }
}
