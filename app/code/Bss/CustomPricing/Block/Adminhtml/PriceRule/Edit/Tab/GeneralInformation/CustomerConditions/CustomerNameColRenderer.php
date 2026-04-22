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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Block\Adminhtml\PriceRule\Edit\Tab\GeneralInformation\CustomerConditions;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

/**
 * Class CustomerNameColRenderer - render name of customer
 */
class CustomerNameColRenderer extends AbstractRenderer
{
    /**
     * @inheritDoc
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $firstName = $row->getData('firstname') ?
            $row->getData('firstname') . "&nbsp;" :
            null;
        $middleName = $row->getData('middlename') ?
            $row->getData('middlename') . "&nbsp;" :
            null;
        $lastName = $row->getData('lastname') ?
            $row->getData('lastname') . "&nbsp;" :
            null;
        return $firstName . $middleName . $lastName;
    }
}
