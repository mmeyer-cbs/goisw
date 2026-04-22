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

use Bss\CompanyCredit\Model\History;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Sales\Api\OrderRepositoryInterface;

class ViewOrder extends AbstractRenderer
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Construct class view order
     *
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
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
        $value = '<span>';
        $type = $row->getType();
        switch ($type) {
            case History::TYPE_ADMIN_REFUND:
            case History::TYPE_ADMIN_CHANGES_CREDIT_LIMIT:
                $value .= "";
                break;
            case History::TYPE_PLACE_ORDER:
                $order = $this->orderRepository->get($row->getOrderId());
                $url = $this->getUrl(
                    'sales/order/view',
                    ['order_id' => $row->getOrderId()]
                );
                $value .= '<a href="' . $url . '"">';
                $value .= __('Order # %1', $order->getIncrementId());
                $value .= '</a>';
                break;
            default:
                break;
        }
        $value .= '</span>';
        return $value;
    }
}
