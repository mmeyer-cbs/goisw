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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Observer\Permission\Additional;

use Bss\SalesRep\Helper\Data;
use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ViewBlockAbstractToHtmlBefore
 *
 * @package Bss\SalesRep\Observer\Permission\Additional
 */
class ViewBlockAbstractToHtmlBefore implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * ViewBlockAbstractToHtmlBefore constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Add tab Additional
     *
     * @param EventObserver $observer
     * @return ViewBlockAbstractToHtmlBefore
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helper->isEnable()) {
            $block = $observer->getEvent()->getBlock();
            if ($block instanceof \Magento\User\Block\User\Edit\Tabs) {
                $block->addTabAfter(
                    'additional_section',
                    [
                        'label' => __('Additional Information'),
                        'title' => __('Additional Information'),
                        'content' => $block->getLayout()->createBlock(
                            \Bss\SalesRep\Block\Adminhtml\Edit\Tab\Additional::class
                        )->toHtml()
                    ],
                    'roles_section'
                );
            }
        }
        return $this;
    }
}
