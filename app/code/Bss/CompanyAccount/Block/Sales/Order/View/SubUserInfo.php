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
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class SubUserInfo
 *
 * @package Bss\CompanyAccount\Block\Sales\Order\View
 */
class SubUserInfo extends Template
{
    /**
     * @var SubUserInfoHelper
     */
    private $subUserInfoHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * SubInfo constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param SubUserInfoHelper $subUserInfoHelper
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        SubUserInfoHelper        $subUserInfoHelper,
        Template\Context         $context,
        OrderRepositoryInterface $orderRepository,
        array                    $data = []
    ) {
        $this->quoteFactory=$quoteFactory;
        $this->subUserInfoHelper = $subUserInfoHelper;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get SubUser information
     *
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    public function getSubUserInfo()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $userInfo = $this->subUserInfoHelper->getSubUserInfo($orderId);
        if (!isset($userInfo['order_request']) && $userInfo) {
            $userInfo['order_request'] = $this->orderRepository->get($orderId)->getQuoteId();
        }
        if ($userInfo) {
            $userInfo['request_status']=$this->quoteFactory->create()
                ->loadByIdWithoutStore($userInfo['order_request'])
                ->getIsActive();
        }

        return $userInfo;
    }

    /**
     * Get order request url
     *
     * @param string|int $requestId
     * @return string
     */
    public function getViewOrderRequestUrl($requestId)
    {
        return $this->getUrl('companyaccount/order/view', ['order_id' => $requestId]);
    }
}
