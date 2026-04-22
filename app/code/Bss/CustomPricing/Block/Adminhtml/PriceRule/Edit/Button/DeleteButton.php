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

namespace Bss\CustomPricing\Block\Adminhtml\PriceRule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Delete button in form
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    const DELETE_URL = '*/*/delete';

    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getPriceRuleId()) {
            $data = [
                'label' => __('Delete'),
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this rule?'
                ) . '\', \'' . $this->getDeleteUrl() . '\', {data: {}})',
                'sort_order' => 20
            ];
        }
        return $data;
    }

    /**
     * Get delete button url.
     *
     * @return string
     */
    private function getDeleteUrl(): string
    {
        return $this->getUrl(
            self::DELETE_URL,
            ['id' => $this->getPriceRuleId()]
        );
    }
}
