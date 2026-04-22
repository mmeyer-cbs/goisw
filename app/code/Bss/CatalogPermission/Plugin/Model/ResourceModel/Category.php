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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Plugin\Model\ResourceModel;

/**
 * Class Category
 *
 * @package Bss\CatalogPermission\Plugin\Model\ResourceModel
 */
class Category
{
    const EE = 'Enterprise';
    const CE = 'Community';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(\Magento\Framework\App\ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * Set edition param
     *
     * @param \Bss\CatalogPermission\Model\ResourceModel\Category $subject
     * @param string $edition
     * @return string
     */
    public function beforeGetColumnNameId($subject, $edition = '')
    {
        if ($this->productMetadata->getEdition() == self::EE)
        {
            return self::EE;
        }
        return self::CE;
    }
    /**
     * Plugin after
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $result
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetChildrenCategories($subject, $result)
    {
        return $result->addAttributeToSelect(
            'bss_customer_group'
        );
    }
}
