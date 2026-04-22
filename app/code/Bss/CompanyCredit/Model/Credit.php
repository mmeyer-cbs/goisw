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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreFactory;
use Bss\CompanyCredit\Model\ResourceModel\Credit as ResourceModelCredit;
use Bss\CompanyCredit\Api\Data\CreditInterface;

class Credit extends AbstractModel implements CreditInterface
{
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param StoreFactory $storeFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreFactory $storeFactory
    ) {

        parent::__construct(
            $context,
            $registry
        );
        $this->storeFactory = $storeFactory;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModelCredit::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreditLimit($creditLimit)
    {
        return $this->setData(self::CREDIT_LIMIT, $creditLimit);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentDueDate($paymentDueDate)
    {
        return $this->setData(self::PAYMENT_DUE_DATE, $paymentDueDate);
    }

    /**
     * {@inheritdoc}
     */
    public function setUsedCredit($usedCredit)
    {
        return $this->setData(self::USED_CREDIT, $usedCredit);
    }

    /**
     * {@inheritdoc}
     */
    public function setAvailableCredit($availableCredit)
    {
        return $this->setData(self::BALANCE_CREDIT, $availableCredit);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowExceed($allowExceed)
    {
        return $this->setData(self::ALLOW_EXCEED, $allowExceed);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditLimit()
    {
        return $this->getData(self::CREDIT_LIMIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentDueDate()
    {
        return $this->getData(self::PAYMENT_DUE_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedCredit()
    {
        return $this->getData(self::USED_CREDIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableCredit()
    {
        return $this->getData(self::BALANCE_CREDIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowExceed()
    {
        return $this->getData(self::ALLOW_EXCEED);
    }

    /**
     * Load store credit by customer
     *
     * @param int $customerId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomer($customerId)
    {
        return $this->_getResource()->loadByCustomer($this, $customerId);
    }
}
