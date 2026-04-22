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

namespace Bss\CompanyAccount\Block\Order;

use Bss\CompanyAccount\Helper\SubUserHelper;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\SubUserQuoteRepository as SubUserQuote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

/**
 * Class RequestTitle
 */
class RequestTitle extends Template
{
    /**
     * @var SubUserQuote
     */
    protected $subUserQuote;

    /**
     * @var SubUserHelper
     */
    protected $subUser;

    /**
     * @var Order
     */
    private $orderModel;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Function construct RequestTitle
     *
     * @param Template\Context $context
     * @param SubUserQuote $subUserQuote
     * @param SubUserHelper $subUser
     * @param Order $orderModel
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context         $context,
        SubUserQuote             $subUserQuote,
        SubUserHelper            $subUser,
        Order                    $orderModel,
        Data                     $helper,
        array                    $data = []
    ) {
        $this->subUserQuote = $subUserQuote;
        $this->subUser = $subUser;
        $this->orderModel = $orderModel;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get order id
     *
     * @return false|mixed
     */
    public function getOrderId()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        try {
            $incrementId = $this->helper->getQuoteById($quoteId)->getReservedOrderId() ?? false;
        } catch (NoSuchEntityException $exception) {
            $this->_logger->critical($exception);
            return false;
        }
        if ($incrementId === false) {
            return false;
        }
        return [$this->orderModel->loadByIncrementId($incrementId)->getId(), $incrementId];
    }

    /**
     * Get view order url
     *
     * @return string
     */
    public function getOrderViewUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getOrderId()[0]]);
    }
}
