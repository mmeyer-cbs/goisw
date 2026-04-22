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

namespace Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer;

use Bss\StoreCredit\Model\History;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Addition
 * @package Bss\StoreCredit\Block\Adminhtml\Grid\Column\Renderer
 */
class Addition extends AbstractRenderer
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $result = '';
        switch ($row->getType()) {
            case History::TYPE_CANCEL:
                if ($row->getOrderIncrementId()) {
                    $url = $this->getUrl(
                        'sales/order/view',
                        ['order_id' => $row->getOrderId()]
                    );
                    $result .= '<a href="' . $url . '"">';
                    $result .= __('Order is Canceled');
                    $result .= '</a>';
                } else {
                    $result .= __('Order is Deleted');
                }
                break;
            case History::TYPE_UPDATE:
                $result = $this->_getValue($row);
                break;
            case History::TYPE_USED_IN_ORDER:
                if ($row->getOrderIncrementId()) {
                    $url = $this->getUrl(
                        'sales/order/view',
                        ['order_id' => $row->getOrderId()]
                    );
                    $result .= '<a href="' . $url . '"">';
                    $result .= __('View Order');
                    $result .= '</a>';
                } else {
                    $result .= __('Order is Deleted');
                }
                break;
            case History::TYPE_REFUND:
                if ($row->getCreditmemoIncrementId()) {
                    $url = $this->getUrl(
                        'sales/creditmemo/view',
                        ['creditmemo_id' => $row->getCreditmemoId()]
                    );
                    $result .= '<a href="' . $url . '"">';
                    $result .= __('View Credit Memo');
                    $result .= '</a>';
                } else {
                    $result .= __('Credit Memo is Deleted');
                }
                break;
            default:
                break;
        }
        return $result;
    }
}
