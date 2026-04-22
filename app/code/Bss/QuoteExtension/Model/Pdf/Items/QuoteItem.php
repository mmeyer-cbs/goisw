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
namespace Bss\QuoteExtension\Model\Pdf\Items;

/**
 * Class QuoteItem
 *
 * @package Bss\QuoteExtension\Model\Pdf\Items
 */
class QuoteItem extends AbstractItems
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $quoteCurrency = null;

    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $configurationPool;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->string = $string;
        $this->currencyFactory = $currencyFactory;
        $this->configurationPool = $configurationPool;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get currency model instance. Will be used currency with which order placed
     *
     * @return \Magento\Directory\Model\Currency
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteCurrency()
    {
        if ($this->quoteCurrency === null) {
            $this->quoteCurrency = $this->currencyFactory->create();
            $this->quoteCurrency->load($this->getQuote()->getQuoteCurrencyCode());
        }
        return $this->quoteCurrency;
    }

    /**
     * Retrieve text formatted price value including order rate
     *
     * @param float $price
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function formatPriceTxt($price)
    {
        return $this->getQuoteCurrency()->formatTxt($price);
    }

    /**
     * Draw item line
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function draw()
    {
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];
        $canShowPrice = $item->getCanShowPrice();

        // draw Product name
        $lines[0] = [['text' => $this->string->split($item->getName(), 25, true, true), 'feed' => 35]];

        // draw SKU
        $lines[0][] = [
            'text' => $this->string->split($item->getSku(), 12, true, true),
            'feed' => 290,
            'align' => 'right',
        ];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 435, 'align' => 'right'];

        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 375;
        $feedSubtotal = $feedPrice + 190;
        foreach ($prices as $priceData) {
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                $i++;
            }
            // draw Price
            $lines[$i][] = [
                'text' => ($canShowPrice) ? $priceData['price'] : '',
                'feed' => $feedPrice,
                'font' => 'bold',
                'align' => 'right',
            ];
            // draw Subtotal
            $lines[$i][] = [
                'text' => ($canShowPrice) ? $priceData['subtotal'] : '',
                'feed' => $feedSubtotal,
                'font' => 'bold',
                'align' => 'right',
            ];
            $i++;
        }

        // draw Tax
        $lines[0][] = [
            'text' => ($canShowPrice) ? $this->formatPriceTxt($item->getTaxAmount()) : '',
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right',
        ];

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                // Checking whether option value is not null
                if ($option['value']!= null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $optionValue = $option['value'];
                        if (is_array($option['value']) && isset($option['value'][0])) {
                            $optionValue = $option['value'][0];
                        }
                        $printValue = $this->filterManager->stripTags($optionValue);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40];
                    }
                }
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }

    /**
     * Retrieve item options
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemOptions()
    {
        $item = $this->getItem();
        return $this->configurationPool->getByProductType($item->getProductType())->getOptions($item);
    }

    /**
     * Get array of arrays with item prices information for display in PDF
     *
     * Format: array(
     *  $index => array(
     *      'label'    => $label,
     *      'price'    => $price,
     *      'subtotal' => $subtotal
     *  )
     * )
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemPricesForDisplay()
    {
        $item = $this->getItem();
        if ($this->_taxData->displaySalesBothPrices()) {
            $prices = [
                [
                    'label' => __('Excl. Tax') . ':',
                    'price' => $this->formatPriceTxt($item->getPrice()),
                    'subtotal' => $this->formatPriceTxt($item->getRowTotal()),
                ],
                [
                    'label' => __('Incl. Tax') . ':',
                    'price' => $this->formatPriceTxt($item->getPriceInclTax()),
                    'subtotal' => $this->formatPriceTxt($item->getRowTotalInclTax())
                ],
            ];
        } elseif ($this->_taxData->displaySalesPriceInclTax()) {
            $prices = [
                [
                    'price' => $this->formatPriceTxt($item->getPriceInclTax()),
                    'subtotal' => $this->formatPriceTxt($item->getRowTotalInclTax()),
                ],
            ];
        } else {
            $prices = [
                [
                    'price' => $this->formatPriceTxt($item->getPrice()),
                    'subtotal' => $this->formatPriceTxt($item->getRowTotal()),
                ],
            ];
        }
        return $prices;
    }
}
