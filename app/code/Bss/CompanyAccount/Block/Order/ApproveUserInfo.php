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
use Bss\CompanyAccount\Model\SubUserQuoteRepository as SubUserQuote;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;

/**
 * Class ApproveUserInfo
 */
class ApproveUserInfo extends Template
{
    const COMPANY_ADMIN_ID = 0;

    /**
     * @var SubUserQuote
     */
    protected $subUserQuote;

    /**
     * @var SubUserHelper
     */
    protected $subUser;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Function construct sub-user info
     *
     * @param Template\Context $context
     * @param SubUserQuote $subUserQuote
     * @param SubUserHelper $subUser
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SubUserQuote     $subUserQuote,
        SubUserHelper    $subUser,
        Session          $customerSession,
        array            $data = []
    ) {
        $this->subUserQuote = $subUserQuote;
        $this->subUser = $subUser;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Get SubUser information
     *
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface|mixed|void
     * @throws \Bss\CompanyAccount\Exception\RelationMethodNotFoundException
     */
    public function getSubUserInfo()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        $approveUserId = $this->subUserQuote->getByQuoteId($quoteId)->getActionBy();
        $subUsers = $this->subUser->getListBy($approveUserId);
        foreach ($subUsers as $subUser) {
            return $subUser;
        }
    }

    /**
     * Function get status to render
     *
     * @return array|mixed|null
     */
    public function getStatusRender()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        $quoteStatus = $this->subUserQuote->getByQuoteId($quoteId)->getQuoteStatus();
        if ($quoteStatus == 'rejected') {
            return 'Rejected';
        }
        if ($quoteStatus == 'approved') {
            return 'Approved';
        }
        return null;
    }

    /**
     * Function check hasActionBy
     *
     * @return bool
     */
    public function hasActionBy()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        $approveUserId = $this->subUserQuote->getByQuoteId($quoteId)->getActionBy();
        $status = $this->subUserQuote->getByQuoteId($quoteId)->getQuoteStatus();
        if ($approveUserId == null && $approveUserId != self::COMPANY_ADMIN_ID || $status == 'waiting') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Function check isCompanyAccount
     *
     * @return bool
     */
    public function isCompanyAccount(): bool
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        $approveUserId = $this->subUserQuote->getByQuoteId($quoteId)->getActionBy();
        if ($approveUserId == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function get customer session
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
