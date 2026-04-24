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
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

class CompatibleCompanyAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Check role create quote dont create order
     *
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @return string|false
     */
    public function roleCompanyAccount($manageQuote)
    {
        $user = $this->customerSession->getData();
        if ($user['customer_id'] == $manageQuote->getCustomerId()) {
            return "place_order";
        }
        return false;
    }

    /**
     * Get url sending order
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlSendOrder()
    {
        return $this->storeManager->getStore()->getUrl("companyaccount/order/sendrequest");
    }
}
