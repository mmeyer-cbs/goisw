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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\B2bRegistration\Helper\Data;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;

class LoginObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * LoginObserver constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * Check Login in Checkout Page
     * @param \Magento\Framework\Event\Observer $observer
     * @throws EmailNotConfirmedException
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $customerValue = $observer->getModel()->getData('b2b_activasion_status');
            if ($customerValue == CustomerAttribute::B2B_PENDING) {
                $message = $this->helper->getPendingMess();
                if ($this->request->isAjax()) {
                    throw new EmailNotConfirmedException(__($message));
                }
            }
            if ($customerValue == CustomerAttribute::B2B_REJECT) {
                $message = $this->helper->getDisapproveMess();
                if ($this->request->isAjax()) {
                    throw new EmailNotConfirmedException(__($message));
                }
            }
        }
    }
}
