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

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class PaymentStatus extends AbstractRenderer
{
    /**
     * @var \Bss\CompanyCredit\Model\UpdatePaymentStatus
     */
    protected $updatePaymentStatus;

    /**
     * PaymentStatus constructor.
     *
     * @param Context $context
     * @param \Bss\CompanyCredit\Model\UpdatePaymentStatus $updatePaymentStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Bss\CompanyCredit\Model\UpdatePaymentStatus $updatePaymentStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->updatePaymentStatus = $updatePaymentStatus;
    }

    /**
     * Render payment status.
     *
     * @param DataObject $row
     * @return string|null
     */
    public function render(DataObject $row)
    {
        return $this->updatePaymentStatus->showPaymentStatus($row->getPaymentStatus(), $row->getPaymentDueDate());
    }
}
