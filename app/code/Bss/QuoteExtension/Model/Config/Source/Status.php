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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model\Config\Source;

/**
 * Class Status.
 *
 * @package Bss\QuoteExtension\Model\Config\Source
 */
class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    const STATE_PENDING = 'pending';
    const STATE_ORDERED = 'ordered';
    const STATE_UPDATED = 'updated';
    const STATE_REJECTED = 'rejected';
    const STATE_RESUBMIT = 'resubmit';
    const STATE_CANCELED = 'cancelled';
    const STATE_EXPIRED = 'expired';
    const STATE_COMPLETE = 'complete';
    const STATE_ORDER_SENT = 'order-sent';

    /**
     * Get Grid row status type labels array.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [
            self::STATE_PENDING => __('Pending'),
            self::STATE_ORDERED => __('Ordered'),
            self::STATE_UPDATED => __('Updated'),
            self::STATE_REJECTED => __('Rejected'),
            self::STATE_CANCELED => __('Closed'),
            self::STATE_RESUBMIT => __('Resubmitted'),
            self::STATE_EXPIRED => __('Expired'),
            self::STATE_COMPLETE => __('Complete'),
            self::STATE_ORDER_SENT => __('Order Sent')
        ];
        return $options;
    }

    /**
     * Get Grid row status labels array with empty value for option element.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
     * Get Grid row type array for option element.
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get Grid row type array for option element.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
