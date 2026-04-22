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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\CustomerAttributes\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity as Entity;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Data patch format
 */
class UpdateCustomerAttributeData implements DataPatchInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * Entity
     *
     * @var Entity
     */
    private $entityModel;

    /**
     * @var \Bss\CustomerAttributes\Model\ResourceModel\Entity\Attribute
     */
    private $attributeModel;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * Construct.
     *
     * @param Config $eavConfig
     * @param \Bss\CustomerAttributes\Model\ResourceModel\Entity\Attribute $attributeModel
     * @param Entity $entityModel
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        Config $eavConfig,
        \Bss\CustomerAttributes\Model\ResourceModel\Entity\Attribute $attributeModel,
        Entity $entityModel,
        ModuleDataSetupInterface $setup
    ) {
        $this->attributeModel = $attributeModel;
        $this->eavConfig = $eavConfig;
        $this->entityModel = $entityModel;
        $this->setup = $setup;
    }

    /**
     * Update customer attribute.
     *
     * @return UpdateCustomerAttributeData|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $setup = $this->setup;

        $setup->startSetup();
        $entityTypeId = $this->entityModel->setType(
            \Magento\Customer\Model\Customer::ENTITY
        )->getTypeId();

        foreach ($this->attributeModel->getAttributeCodes($setup, $entityTypeId) as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute('customer', $attributeCode);
            $usedInForms = $attribute->getUsedInForms();
            if (!in_array('is_customer_attribute', $usedInForms)) {
                array_push($usedInForms, 'is_customer_attribute');
                $attribute->setData('used_in_forms', $usedInForms);
                $this->saveAttribute($attribute);
            }
        }

        $setup->endSetup();
    }

    /**
     * @param mixed $attribute
     * @return mixed
     */
    private function saveAttribute($attribute)
    {
        return $attribute->save();
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
