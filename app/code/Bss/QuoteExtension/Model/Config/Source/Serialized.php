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

namespace Bss\QuoteExtension\Model\Config\Source;

/**
 * Class Serialized
 *
 * @package Bss\QuoteExtension\Model\Config\Source
 */
class Serialized extends \Magento\Framework\App\Config\Value
{
    /**
     * Bss Helper Mininum Amount
     *
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\Amount
     */
    protected $helperAmount;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\Amount $helperAmount
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Bss\QuoteExtension\Helper\QuoteExtension\Amount $helperAmount,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperAmount = $helperAmount;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Unserialize option value after load
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $value = $this->helperAmount->makeArrayFieldValue($value);
            $this->setValue($value);
        }
    }

    /**
     * Serialize option value before save
     *
     * @return \Magento\Framework\App\Config\Value|void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->helperAmount->makeStorableArrayFieldValue($value);
        $this->setValue($value);
    }
}
