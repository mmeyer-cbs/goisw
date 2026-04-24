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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Items\Renderer;

/**
 * Class DefaultRenderer
 */
class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $helperFactory;

    /**
     * DefaultRenderer constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Catalog\Helper\ImageFactory $helperFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Catalog\Helper\ImageFactory $helperFactory,
        array $data = []
    ) {
        $this->helperFactory  = $helperFactory;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $messageHelper,
            $checkoutHelper,
            $data
        );
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('mage_quote');
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImage($product, $imageId)
    {
        $image = $this->helperFactory->create()->init($product, $imageId)
            ->constrainOnly(true)
            ->keepAspectRatio(true)
            ->keepTransparency(true)
            ->keepFrame(false)
            ->resize(200, 300);

        return $image;
    }
}
