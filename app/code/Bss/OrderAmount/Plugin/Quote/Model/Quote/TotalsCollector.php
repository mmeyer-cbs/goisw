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
 * @package    Bss_OrderAmount
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderAmount\Plugin\Quote\Model\Quote;

/**
 * Class TotalsCollector
 *
 * @package Bss\OrderAmount\Plugin\Quote\Model\Quote
 */
class TotalsCollector
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\OrderAmount\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * TotalsCollector constructor.
     * @param \Bss\OrderAmount\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Bss\OrderAmount\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Quote\Model\Quote\TotalsCollector $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollectAddressTotals(
        \Magento\Quote\Model\Quote\TotalsCollector $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address $address
    ) {
        $storeId = $quote->getStoreId();
        $validateEnabled = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        // check region before save - bug magento 2
        if (is_array($address->getRegion())) {
            $regionData = $address->getRegion();
            if (array_key_exists('region_code', $regionData)) {
                $address->setRegionCode($regionData['region_code']);
            }
            if (array_key_exists('region_id', $regionData)) {
                $address->setRegionId($regionData['region_id']);
            }
            $address->setRegion(null);
        }

        $result = $proceed($quote, $address);
        if ($validateEnabled) {
            try {
                $address->save();
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        return $result;
    }
}
