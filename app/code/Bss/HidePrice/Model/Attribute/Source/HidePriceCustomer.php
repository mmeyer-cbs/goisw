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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Model\Attribute\Source;

class HidePriceCustomer extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const BSS_HIDE_PRICE_USE_CONFIG = 2;
    const BSS_HIDE_PRICE_ENABLE = 1;
    const BSS_HIDE_PRICE_DISABLE = 0;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory
     */
    protected $_eavAttributeFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $attributeFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $attributeFactory
    ) {
        $this->_eavAttributeFactory = $attributeFactory;
    }

    /**
     * Get Options For Config Pre Test Product Page.
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Please Select'), 'value' => self::BSS_HIDE_PRICE_USE_CONFIG],
                ['label' => __('Enable'), 'value' => self::BSS_HIDE_PRICE_ENABLE],
                ['label' => __('Disable'), 'value' => self::BSS_HIDE_PRICE_DISABLE],
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => $attributeCode . ' column',
            ],
        ];
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_eavAttributeFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
