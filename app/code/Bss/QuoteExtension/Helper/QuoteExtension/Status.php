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
namespace Bss\QuoteExtension\Helper\QuoteExtension;

/**
 * Class Status
 *
 * @package Bss\QuoteExtension\Helper\QuoteExtension
 */
class Status extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\QuoteExtension\Model\Config\Source\Status
     */
    protected $status;

    /**
     * Status constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Bss\QuoteExtension\Model\Config\Source\Status $status
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Bss\QuoteExtension\Model\Config\Source\Status $status
    ) {
        parent::__construct($context);
        $this->status = $status;
    }

    /**
     * Get Status Label
     *
     * @param string $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        $array = $this->status->getOptionArray();
        return isset($array[$status]) ? $array[$status] : '';
    }
}
