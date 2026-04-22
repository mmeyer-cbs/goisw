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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History;

use Bss\CompanyCredit\Helper\Currency as HelperCurrency;
use Bss\CompanyCredit\Model\UpdatePaymentStatus;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class UnpaidCredit extends AbstractRenderer
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $helperCurrency;

    /**
     * @var UpdatePaymentStatus
     */
    protected $paymentStatus;

    /**
     * UnpaidCredit constructor.
     *
     * @param Context $context
     * @param HelperCurrency $helperCurrency
     * @param UpdatePaymentStatus $paymentStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperCurrency $helperCurrency,
        UpdatePaymentStatus $paymentStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperCurrency = $helperCurrency;
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * Format unpaid credit
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        if ($this->_getValue($row)) {
            return $this->helperCurrency->formatPrice($this->_getValue($row), $row->getCurrencyCode());
        } elseif (
            $this->paymentStatus
                ->showPaymentStatus($row->getPaymentStatus(), $row->getPaymentDueDate()) === 'Overdue'
        ) {
            $absChangeCredit = $row->getChangeCredit() ? abs($row->getChangeCredit()) : 0;
            return $this->helperCurrency->formatPrice($absChangeCredit, $row->getCurrencyCode());
        }

        return '';
    }
}
