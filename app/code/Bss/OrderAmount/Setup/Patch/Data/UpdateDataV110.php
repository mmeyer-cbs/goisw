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
 * @package    Bss_OrderAmount
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\OrderAmount\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Data patch format
 */
class UpdateDataV110 implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Construct.
     *
     * @param ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->setup = $setup;
    }

    /**
     * Update data ver 110.
     *
     * @return UpdateDataV110|void
     */
    public function apply()
    {
        $installer = $this->setup;
        $installer->startSetup();
        $readAdapter = $installer->getConnection('core_read');
        $writeAdapter = $installer->getConnection('core_write');
        $tableName = $installer->getTable('core_config_data');
        //@codingStandardsIgnoreStart
        $select = $readAdapter->select()
            ->from(
                [$tableName],
                ['config_id', 'value']
            )
            ->where("path LIKE '%sales/minimum_order/amount%'");
        $old_config_data = $readAdapter->fetchAll($select);
        foreach ($old_config_data as $result) {
            if (preg_match('/^((s|i|d|b|a|O|C):|N;)/', $result['value'])) {
                $value = $this->serializer->unserialize($result['value']);
                $convert = $this->serializer->serialize($value);
                $writeAdapter->update($tableName, ['value' => $convert], ['config_id = ?' => $result['config_id']]);
            }
        }
        //@codingStandardsIgnoreEnd

        $installer->endSetup();
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
        return '1.1.0';
    }
}
