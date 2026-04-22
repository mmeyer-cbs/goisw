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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\QuoteExtension\View;

use Bss\QuoteExtension\Block\QuoteExtension\View;
use Bss\QuoteExtension\Helper\Data as QuoteHelper;
use Bss\QuoteExtension\Helper\QuoteExtension\Version as QuoteVersionHelper;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class Action
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension\View
 */
class Action extends View
{
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * Action constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Registry $coreRegistry
     * @param QuoteHelper $helper
     * @param QuoteVersionHelper $versionHelper
     * @param TaxHelper $taxHelper
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Registry $coreRegistry,
        QuoteHelper $helper,
        QuoteVersionHelper $versionHelper,
        TaxHelper $taxHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $coreRegistry,
            $helper,
            $versionHelper,
            $taxHelper,
            $data
        );
        $this->urlHelper = $urlHelper;
    }

    /**
     * @var array[]
     */
    protected $status = [
        'cancel' => [
            Status::STATE_PENDING,
            Status::STATE_UPDATED,
            Status::STATE_RESUBMIT,
            Status::STATE_EXPIRED,
            Status::STATE_REJECTED
        ],
        'print' => [
            Status::STATE_PENDING,
            Status::STATE_UPDATED,
            Status::STATE_RESUBMIT,
            Status::STATE_EXPIRED,
            Status::STATE_REJECTED,
            Status::STATE_COMPLETE,
            Status::STATE_ORDER_SENT
        ],
        'checkout' => [
            Status::STATE_UPDATED
        ],
        'delete' => [
            Status::STATE_PENDING,
            Status::STATE_UPDATED,
            Status::STATE_RESUBMIT,
            Status::STATE_EXPIRED,
            Status::STATE_REJECTED,
            Status::STATE_COMPLETE,
        ]
    ];

    /**
     * Get action button submit
     *
     * @param string $action
     * @param array $data
     * @return string
     */
    public function getAction($action, $data = ['_secure' => true])
    {
        return $this->getUrl($action, $data);
    }

    /**
     * Check can show button
     *
     * @param string $status
     * @return bool
     */
    public function canShowButton($status)
    {
        $currentStatus = $this->getRequestQuote()->getStatus();
        return in_array($currentStatus, $this->status[$status]);
    }

    /**
     * Can move checkout
     *
     * @return bool
     */
    public function canMoveCheckout()
    {
        $quote = $this->getRequestQuote();
        $moveCheckout = $quote->getMoveCheckout();
        if (is_numeric($moveCheckout) && !$moveCheckout) {
            return false;
        }
        return in_array($quote->getStatus(), $this->status["checkout"]);
    }
}
