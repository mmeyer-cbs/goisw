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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Block\Adminhtml;

use Bss\CustomerAttributes\Helper\Data as HelperData;
use Magento\Framework\View\Element\Template;

/**
 * Class EditJs
 * @package Bss\CustomerAttributes\Block\Adminhtml
 */
class EditJs extends \Magento\Framework\View\Element\Template
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * EditJs constructor.
     * @param HelperData $helperData
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        HelperData $helperData,
        Template\Context $context,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Check display customer grid (type date)
     *
     * @return bool
     */
    public function notDisplayCustomerGrid()
    {
        if ($this->helperData->checkVersionMagento() >= "2.4.0") {
            return 1;
        }
        return 0;
    }
}
