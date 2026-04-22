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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
//@codingStandardsIgnoreStart
namespace Bss\CustomPricing\Ui\Component\Listing\ProductPrice\Column;

use Bss\CustomPricing\Helper\Data;
use Bss\CustomPricing\Model\Config\Source\PriceTypeOption;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Format currency
 */
class Price extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Price constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Data $helper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $websiteId = $this->context->getRequestParam('website_id');
            $website = $this->storeManager->getWebsite($websiteId);
            $baseCurrencyCode = $this->helper->getBaseCurrencyCode($website->getCode());
            $currency = $this->localeCurrency->getCurrency($baseCurrencyCode);
            foreach ($dataSource['data']['items'] as & $item) {
                if (in_array($item['price_type'], $this->getNoFormatPriceType())) {
                    continue;
                }
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $currency->toCurrency(sprintf("%f", $item[$fieldName]));
                }
            }
        }
        return $dataSource;
    }

    /**
     * @return array
     */
    public function getNoFormatPriceType()
    {
        return [PriceTypeOption::INCREASE_PERCENT_PRICE, PriceTypeOption::DECREASE_PERCENT_PRICE];
    }
}
//@codingStandardsIgnoreEnd
