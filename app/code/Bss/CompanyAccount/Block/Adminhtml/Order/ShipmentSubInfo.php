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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Block\Adminhtml\Order;

use Bss\CompanyAccount\Block\Sales\SubUserInfoHelper;
use Magento\Backend\Block\Template;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ShipmentSubInfo
 *
 * @package Bss\CompanyAccount\Block\Adminhtml\Order
 */
class ShipmentSubInfo extends Template
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
     * ShipmentSubInfo constructor.
     *
     * @param SubUserInfoHelper $subUserInfoHelper
     * @param Template\Context $context
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        SubUserInfoHelper $subUserInfoHelper,
        Template\Context $context,
        ShipmentRepositoryInterface $shipmentRepository,
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
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserOrderInterface
     */
    public function getSubUserInfo()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
            return $this->subUserInfoHelper->getSubUserInfo($shipment->getOrderId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }
}
