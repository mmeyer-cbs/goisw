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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Block\Customer;

use Bss\CompanyCredit\Helper\Data as HelperData;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Link constructor.
     * @param HelperData $helperData
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        HelperData $helperData,
        Context $context,
        DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Render tab company credit if enable module
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helperData->isEnableModule()) {
            return parent::_toHtml();
        }
        return "";
    }
}
