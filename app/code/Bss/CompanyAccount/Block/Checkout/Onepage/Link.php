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

namespace Bss\CompanyAccount\Block\Checkout\Onepage;

use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Helper\Data as Helper;
use Bss\CompanyAccount\Helper\QuoteHelper;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;

/**
 * One page checkout cart link
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $checkoutHelper;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Data $checkoutHelper
     * @param QuoteHelper $quoteHelper
     * @param CustomerSession $customerSession
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context         $context,
        Session         $checkoutSession,
        Data            $checkoutHelper,
        QuoteHelper     $quoteHelper,
        CustomerSession $customerSession,
        Helper          $helper,
        array           $data = []
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->quoteHelper = $quoteHelper;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout');
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->checkoutSession->getQuote()->validateMinimumAmount();
    }

    /**
     * @return bool
     */
    public function isPossibleOnepageCheckout()
    {
        return $this->checkoutHelper->canOnepageCheckout();
    }

    /**
     * Check visible button Back to your cart
     *
     * @return bool|int
     */
    public function isCurrentQuote()
    {
        $check = false;
        if ($this->helper->isCompanyAccount()) {
            ($subUser = $this->customerSession->getSubUser())
                ? $check = $this->quoteHelper->checkQuote(
                    $subUser->getId(),
                    SubUserQuoteInterface::SUB_USER_ID
                ) : $check = $this->quoteHelper->checkQuote(
                $this->customerSession->getCustomerId(),
                SubUserQuoteInterface::CUSTOMER_ID
            );
        }
        return (bool) $check;
    }

    /**
     * Get back cart url
     *
     * @return string
     */
    public function getBackCartUrl()
    {
        return $this->getUrl('companyaccount/checkout/backtoquote', ['reload' => 'true']);
    }
}
