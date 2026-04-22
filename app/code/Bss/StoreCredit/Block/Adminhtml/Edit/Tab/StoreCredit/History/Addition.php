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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Block\Adminhtml\Edit\Tab\StoreCredit\History;

use Bss\StoreCredit\Model\History;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Addition
 * @package Bss\StoreCredit\Block\Adminhtml\Edit\Tab\StoreCredit\History
 */
class Addition extends AbstractRenderer
{
    /**
     * Renders a column
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = '<span>';
        $type = $row->getType();
        switch ($type) {
            case History::TYPE_UPDATE:
                $value .= $row->getCommentContent();
                break;
            case History::TYPE_USED_IN_ORDER:
            case History::TYPE_CANCEL:
                if ($row->getOrderIncrementId()) {
                    $url = $this->getUrl(
                        'sales/order/view',
                        ['order_id' => $row->getOrderId()]
                    );
                    $value .= '<a href="' . $url . '"">';
                    $value .= __('Order # %1', $row->getOrderIncrementId());
                    $value .= '</a>';
                } else {
                    $value .= __('Order is Deleted');
                }
                break;
            case History::TYPE_REFUND:
                if ($row->getCreditmemoIncrementId()) {
                    $url = $this->getUrl(
                        'sales/creditmemo/view',
                        ['creditmemo_id' => $row->getCreditmemoId()]
                    );
                    $value .= '<a href="' . $url . '"">';
                    $value .= __('Credit Memo # %1', $row->getCreditmemoIncrementId());
                    $value .= '</a>';
                } else {
                    $value .= __('Credit Memo is Deleted');
                }
                break;
            default:
                break;
        }

        $value .= '</span>';
        return $value;
    }
}
