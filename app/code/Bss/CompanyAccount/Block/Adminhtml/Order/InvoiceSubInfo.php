<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Block\Adminhtml\Order;

use Bss\CompanyAccount\Block\Sales\SubUserInfoHelper;
use Magento\Backend\Block\Template;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InvoiceSubInfo
 *
 * @package Bss\CompanyAccount\Block\Adminhtml\Order
 */
class InvoiceSubInfo extends Template
{
    /**
     * @var SubUserInfoHelper
     */
    private $subUserInfoHelper;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InvoiceSubInfo constructor.
     *
     * @param SubUserInfoHelper $subUserInfoHelper
     * @param Template\Context $context
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        SubUserInfoHelper $subUserInfoHelper,
        Template\Context $context,
        InvoiceRepositoryInterface $invoiceRepository,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->subUserInfoHelper = $subUserInfoHelper;
        $this->invoiceRepository = $invoiceRepository;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * Get SubUser information
     *
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserOrderInterface
     */
    public function getSubUserInfo()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        try {
            $invoice = $this->invoiceRepository->get($invoiceId);
            return $this->subUserInfoHelper->getSubUserInfo($invoice->getOrderId());
        } catch (\Exception $e) {
            $this->logger->debug($e);
            return false;
        }
    }
}
