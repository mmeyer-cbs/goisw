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
namespace Bss\CompanyAccount\Block\Sales\Order\View;

use Bss\CompanyAccount\Block\Sales\SubUserInfoHelper;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InvoiceSubUserInfo
 *
 * @package Bss\CompanyAccount\Block\Sales\Order\View
 */
class InvoiceSubUserInfo extends Template
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
     * InvoiceSubUserInfo constructor.
     *
     * @param SubUserInfoHelper $subUserInfoHelper
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param Template\Context $context
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        SubUserInfoHelper $subUserInfoHelper,
        InvoiceRepositoryInterface $invoiceRepository,
        Template\Context $context,
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
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    public function getSubUserInfo()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');
        try {
            if (!$orderId) {
                $invoice = $this->invoiceRepository->get($invoiceId);
                $orderId = $invoice->getOrderId();
            }
            return $this->subUserInfoHelper->getSubUserInfo($orderId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }
}
