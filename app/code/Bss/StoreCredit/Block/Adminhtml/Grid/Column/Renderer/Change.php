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
namespace Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Ch
 */
class Change extends AbstractRenderer
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
     * Format price column balance by currency
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $class = 'bss-red';
        $value= $row->getChangeAmount();
        $currencyCode = $this->currency->getCreditCurrencyCode($row->getCreditCurrencyCode(), $row->getWebsiteId());
        $changeAmountStoreView = null;
        if ($row->getChangeAmountStoreView() && $row->getCurrencyCode() != $currencyCode) {
            $changeAmountStoreView ="  (" . $this->currency->formatPrice($row->getChangeAmountStoreView(), $row->getCurrencyCode()) . ")";
        }
        $currency =  $this->currency->formatPrice($value, $currencyCode);
        if ($this->_getValue($row) > 0) {
            $class = 'bss-green';
            $currency = '+'. $this->currency->formatPrice($value, $currencyCode);
        }
        return '<span class="' . $class . '"><span>' . $currency . $changeAmountStoreView . '</span></span>';

    }
}
