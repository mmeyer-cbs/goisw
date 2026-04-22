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
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ShipmentSubUserInfo
 *
 * @package Bss\CompanyAccount\Block\Sales\Order\View
 */
class ShipmentSubUserInfo extends Template
{
    /**
     * @var SubUserInfoHelper
     */
    private $subUserInfoHelper;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ShipmentSubUserInfo constructor.
     *
     * @param SubUserInfoHelper $subUserInfoHelper
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param Template\Context $context
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        SubUserInfoHelper $subUserInfoHelper,
        ShipmentRepositoryInterface $shipmentRepository,
        Template\Context $context,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->subUserInfoHelper = $subUserInfoHelper;
        $this->shipmentRepository = $shipmentRepository;
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
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        try {
            if (!$orderId) {
                $invoice = $this->shipmentRepository->get($shipmentId);
                $orderId = $invoice->getOrderId();
            }
            return $this->subUserInfoHelper->getSubUserInfo($orderId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }
}
