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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer\Statistic;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Change
 */
class Balance extends AbstractRenderer
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    private $currency;

    /**
     * Balance constructor.
     *
     * @param Context $context
     * @param \Bss\StoreCredit\Model\Currency $currency
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Bss\StoreCredit\Model\Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currency = $currency;
    }

    /**
     * Format price column change by currency
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $currencyCode = $this->currency->getCreditCurrencyCode($row->getCurrencyCode(), $row->getWebsiteId());
        return $this->currency->formatPrice($this->_getValue($row), $currencyCode);
    }
}
