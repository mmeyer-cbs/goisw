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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Model\Config\Source\Email;

use Magento\Framework\DataObject;
use Magento\Framework\Option\ArrayInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;

/**
 * Class Template
 * @package Bss\StoreCredit\Model\Config\Source\Email
 */
class Template extends DataObject implements ArrayInterface
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    private $templatesFactory;

    /**
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param array $data
     */
    public function __construct(
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
    }

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $collection \Magento\Email\Model\ResourceModel\Template\Collection */
        $collection = $this->templatesFactory->create()->load();
        $options = $collection->toOptionArray();
        $templateLabel = $this->emailConfig->getTemplateLabel('update_store_credit');
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => 'update_store_credit', 'label' => $templateLabel]);
        return $options;
    }
}
