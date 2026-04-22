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

namespace Bss\StoreCredit\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Controller\AbstractAccount;
use Bss\StoreCredit\Helper\Data;

/**
 * Class Account
 *
 * @package Bss\StoreCredit\Controller\Customer
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Account extends AbstractAccount
{
    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * @param Context $context
     * @param Data $bssStoreCreditHelper
     */
    public function __construct(
        Context $context,
        Data $bssStoreCreditHelper
    ) {
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        parent::__construct($context);
    }

    /**
     * Get store credit tab content
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->bssStoreCreditHelper->getGeneralConfig('active')) {
            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->set(__('My Store Credit'));
            $this->_view->renderLayout();
        }
    }
}
