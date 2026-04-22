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
namespace Bss\CompanyCredit\Block\Adminhtml\Edit\Tab\CompanyCredit\History;

use Bss\CompanyCredit\Helper\Data as HelperData;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Type extends AbstractRenderer
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Construct class view order
     *
     * @param HelperData $helperData
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        HelperData  $helperData,
        Context $context,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Renders a column
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        return $this->helperData->getTypeAction($row->getType(), $row->getAllowExceed());
    }
}
