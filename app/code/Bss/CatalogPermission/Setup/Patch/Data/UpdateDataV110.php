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

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Validator\ValidateException;

class UpdateDataV110 implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory          $eavSetupFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup = $setup;
    }

    /**
     * Add and remove attribute
     * @return void
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply()
    {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
            $eavSetup->addAttribute(
                Category::ENTITY,
                'bss_redirect_type',
                [
                    'group' => 'catalog_permission',
                    'label' => 'Bss Redirect Type',
                    'type' => 'int',
                    'input' => 'select',
                    'required' => false,
                    'sort_order' => 80,
                    'global' => Attribute::SCOPE_STORE,
                    'used_in_product_listing' => true,
                    'source' => \Bss\CatalogPermission\Model\Category\Attribute\Source\RedirectType::class
                ]
            );
            $eavSetup->addAttribute(
                Category::ENTITY,
                'bss_select_page',
                [
                    'group' => 'catalog_permission',
                    'label' => 'Bss Select Page',
                    'type' => 'varchar',
                    'input' => 'select',
                    'required' => false,
                    'sort_order' => 85,
                    'global' => Attribute::SCOPE_STORE,
                    'used_in_product_listing' => true,
                    'source' => \Bss\CatalogPermission\Model\Config\Source\BssListCmsPage::class
                ]
            );
            $eavSetup->addAttribute(
                Category::ENTITY,
                'bss_custom_url',
                [
                    'group' => 'catalog_permission',
                    'label' => 'Bss Redirect Type',
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 90,
                    'global' => Attribute::SCOPE_STORE,
                    'used_in_product_listing' => true
                ]
            );
            $eavSetup->addAttribute(
                Category::ENTITY,
                'bss_error_message',
                [
                    'group' => 'catalog_permission',
                    'label' => 'Bss Error Message',
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 95,
                    'global' => Attribute::SCOPE_STORE,
                    'used_in_product_listing' => true
                ]
            );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
