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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Search;

use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\DataProvider\ProductCollection;

/**
 * Class Grid
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Search
 */
class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid
{

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManage;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManage
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param array $data
     * @param ProductCollection|null $productCollectionProvider
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManage,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\Config $salesConfig,
        array $data = [],
        ProductCollection $productCollectionProvider = null
    ) {
        parent::__construct($context, $backendHelper, $productFactory, $catalogConfig, $sessionQuote, $salesConfig, $data, $productCollectionProvider);
        $this->objectManage = $objectManage;
        $this->productMetadata = $productMetadata;
        if ($this->checkVerionMagentoHigher245()) {
            $viewModel = $this->objectManage->create(\Magento\Backend\ViewModel\LimitTotalNumberOfProductsInGrid::class);
            $this->setData('view_model', $viewModel);
        }
    }

    /**
     * Check version magento higher 245
     *
     * @return bool|int
     */
    public function checkVerionMagentoHigher245()
    {
        return version_compare($this->productMetadata->getVersion(), '2.4.5', '>');
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'bss_quote_extension/*/loadBlock',
            ['block' => 'search_grid', '_current' => true, 'collapse' => null]
        );
    }
}
