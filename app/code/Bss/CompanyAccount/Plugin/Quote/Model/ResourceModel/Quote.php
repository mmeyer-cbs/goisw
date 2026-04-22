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
namespace Bss\CompanyAccount\Plugin\Quote\Model\ResourceModel;

use Bss\CompanyAccount\Helper\Data;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Class Quote
 *
 * @package Bss\CompanyAccount\Plugin\Quote\Model\ResourceModel
 */
class Quote
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Snapshot
     */
    private $entitySnapshot;

    /**
     * Quote constructor.
     *
     * @param Snapshot $entitySnapshot
     * @param Data $helper
     */
    public function __construct(
        Snapshot $entitySnapshot,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->entitySnapshot = $entitySnapshot;
    }

    /**
     * Load quote data by customer identifier
     *
     * @param \Magento\Quote\Model\ResourceModel\Quote $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $customerId
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundLoadByCustomerId(
        \Magento\Quote\Model\ResourceModel\Quote $subject,
        callable $proceed,
        $quote,
        $customerId
    ) {
        if ($this->helper->isEnable()) {
            $connection = $subject->getConnection();
            $select = $this->_getLoadSelect(
                $subject,
                'customer_id',
                $customerId,
                $quote
            )->where(
                'bss_is_sub_quote IS NULL'
            )->where(
                'is_active = ?',
                1
            );

            if ($this->helper->isModuleOutputEnabled('Bss_QuoteExtension')) {
                $select->where(
                    'quote_extension IS NULL'
                );
            }
            $select->order(
                'updated_at ' . \Magento\Framework\DB\Select::SQL_DESC
            )->limit(
                1
            );

            $data = $connection->fetchRow($select);

            if ($data) {
                $quote->setData($data);
                $quote->setOrigData();
            }

            $this->_afterLoad($quote);

            return $this;
        }
        return $proceed($quote, $customerId);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $quote
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getLoadSelect($quoteResource, $field, $value, $quote)
    {
        $field = $quoteResource->getConnection()
            ->quoteIdentifier(sprintf('%s.%s', $quoteResource->getMainTable(), $field));

        $select = $quoteResource->getConnection()
            ->select()->from($quoteResource->getMainTable())->where($field . '=?', $value);

        $storeIds = $quote->getSharedStoreIds();
        if ($storeIds) {
            if ($storeIds != ['*']) {
                $select->where('store_id IN (?)', $storeIds);
            }
        } else {
            /**
             * For empty result
             */
            $select->where('store_id < ?', 0);
        }

        return $select;
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->entitySnapshot->registerSnapshot($object);
        return $this;
    }
}
