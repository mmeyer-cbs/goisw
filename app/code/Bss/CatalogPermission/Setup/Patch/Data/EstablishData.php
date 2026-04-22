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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\CatalogPermission\Setup\Patch\Data;

use Bss\CatalogPermission\Model\Category\Attribute\Backend\CustomBackend;
use Bss\CatalogPermission\Model\Category\Attribute\Source\CustomSource;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validator\ValidateException;

class EstablishData implements DataPatchInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface;
     */
    private $setup;

    /**
     * @var \Magento\Sales\Model\Order\StatusFactory
     */
    private $orderStatusFactory;

    /**
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory                $eavSetupFactory,
        \Magento\Sales\Model\Order\StatusFactory          $orderStatusFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->orderStatusFactory = $orderStatusFactory;
        $this->setup = $setup;
    }

    /**
     * Install Setup PreOrder
     *
     * @throws LocalizedException
     * @throws ValidateException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
        $eavSetup->addAttribute(
            Category::ENTITY,
            'bss_customer_group',
            [
                'group' => 'catalog_permission',
                'label' => 'Customer Group',
                'type' => 'varchar',
                'input' => 'multiselect',
                'required' => false,
                'sort_order' => 9,
                'global' => Attribute::SCOPE_STORE,
                'used_in_product_listing' => true,
                'source' => CustomSource::class,
                'backend' => CustomBackend::class
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
