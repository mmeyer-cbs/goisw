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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Paymentshipping\Observer;

use Bss\Paymentshipping\Helper\Data;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ActivePaymentMethod implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $bssHelper;

    /**
     * @var Quote
     */
    protected $backendQuoteSession;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * ActivePaymentMethod constructor.
     * @param Data $bssHelper
     * @param Quote $backendQuoteSession
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        Data $bssHelper,
        Quote $backendQuoteSession,
        \Magento\Framework\App\State $appState
    ) {
        $this->bssHelper = $bssHelper;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->appState = $appState;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $result = $observer->getResult();
        $method = $observer->getMethodInstance()->getCode();
        $myHelperData = $this->bssHelper;
        if (!$myHelperData->canUseMethod($method, 'payment')) {
            $result->setData('is_available', false);
        }
    }
}
