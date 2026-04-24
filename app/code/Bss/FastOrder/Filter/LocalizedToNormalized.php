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
 * @category  BSS
 * @package   Bss_FastOrder
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FastOrder\Filter;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\ObjectManagerInterface;

class LocalizedToNormalized
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param RequestQuantityProcessor $quantityProcessor
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        ObjectManagerInterface $objectManager,
        RequestQuantityProcessor $quantityProcessor
    ) {
        $this->productMetadata = $productMetadata;
        $this->objectManager = $objectManager;
        $this->quantityProcessor = $quantityProcessor;
    }

    /**
     * Filter local value.
     *
     * @param string $qty
     * @return array|string
     */
    public function filter($qty)
    {
        $filter = new \Magento\Framework\Filter\LocalizedToNormalized(
            ['locale' => $this->objectManager->get(
                \Magento\Framework\Locale\ResolverInterface::class
            )->getLocale()]
        );
        if ($this->checkVerionMagentoHigher245()) {
            $qty = $this->quantityProcessor->prepareQuantity($qty);
        }
        return $filter->filter($qty);
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
}
