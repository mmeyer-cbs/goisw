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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Plugin\Cron;

use Magento\Store\Model\StoresConfig;

/**
 * Class CleanExpiredQuotes
 *
 * @package Bss\QuoteExtension\Plugin\Cron
 */
class CleanExpiredQuotes
{
    const LIFETIME = 86400;

    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * CleanExpiredQuotes constructor.
     * @param StoresConfig $storesConfig
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->storesConfig = $storesConfig;
        $this->quoteCollectionFactory = $collectionFactory;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Ignore quote_extension to expired quote
     *
     * @param \Magento\Sales\Cron\CleanExpiredQuotes $subject
     * @param callable $proceed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\Sales\Cron\CleanExpiredQuotes $subject,
        callable $proceed
    ) {
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.3.4', '<')) {
            return $proceed();
        }
        $lifetimes = $this->storesConfig->getStoresConfigByPath('checkout/cart/delete_quote_after');
        foreach ($lifetimes as $storeId => $lifetime) {
            $lifetime *= self::LIFETIME;

            /** @var $quotes \Magento\Quote\Model\ResourceModel\Quote\Collection */
            $quotes = $this->quoteCollectionFactory->create();

            $quotes->addFieldToFilter('store_id', $storeId);
            $quotes->addFieldToFilter('updated_at', ['to' => date("Y-m-d", time() - $lifetime)]);
            $quotes->addFieldToFilter('quote_extension', ['null' => true]);
            $quotes->walk('delete');
        }
    }
}
