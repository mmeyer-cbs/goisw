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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\B2bRegistration\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Data patch format
 */
class UpgradeTBCustomerFormAttributeV125 implements DataPatchInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * UpgradeData table customer_form_attribute constructor.
     *
     * @param ModuleDataSetupInterface $setup
     * @param Config $eavConfig
     * @param CustomerSetupFactory $customerSetupFactory
     * @param IndexerRegistry $indexerRegistry
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        ResourceConnection $resourceConnection
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
        $this->eavConfig = $eavConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Upgrade table customer_form_attribute.
     *
     * @return void
     */
    public function apply()
    {
        $data = [];
        $attribute = $this->getAttributeData();

        if (!empty($attribute)) {
            foreach ($attribute as $item) {
                $data[] = [
                    'form_code' => 'b2b_account_create',
                    'attribute_id' => $item['attribute_id']
                ];
            }
        }

        try {
            $this->setup->getConnection()->insertMultiple($this->setup->getTable('customer_form_attribute'), $data);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Get attribute data before insert new attribute
     *
     * @return array
     */
    public function getAttributeData()
    {
        $connect = $this->resourceConnection->getConnection();
        $listAttribute =
            "(
                'prefix',
                'firstname',
                'middlename',
                'lastname',
                'suffix',
                'email',
                'dob',
                'taxvat',
                'created_at',
                'gender'
            )";
        $eav = $this->setup->getTable('eav_attribute');
        $customerAttribute = $this->setup->getTable('customer_form_attribute');
        $select = sprintf(
                "select eav.attribute_id,eav.attribute_code from %s as eav where entity_type_id=%s
                and attribute_code in %s and attribute_id
                not in ( select main_table.attribute_id from %s as main_table inner join %s as eav
                on eav.attribute_id=main_table.attribute_id where main_table.form_code='b2b_account_create'
            )",
            $eav,
            1,
            $listAttribute,
            $customerAttribute,
            $eav
        );

        return $connect->fetchAll($select);
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

    /**
     * Compare ver module.
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.2.5';
    }
}
