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
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class UpdateCredit extends AbstractRenderer
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $helperCurrency;

    /**
     * UnpaidCredit constructor.
     *
     * @param Context $context
     * @param HelperCurrency $helperCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperCurrency $helperCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperCurrency = $helperCurrency;
    }

    /**
     * Format unpaid credit
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        if ($row->getType() != 1 || $row->getPaymentStatus() == 'Total Paid') {
            return '';
        }

        return '<input class="hide-arrows" name="bss_companycredit[update_paid]['
            . $row->getId() .
            ']" data-form-part="customer_form" type="number" required>';
    }
}
