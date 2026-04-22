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
namespace Bss\CompanyCredit\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Controller\AbstractAccount;
use Bss\CompanyCredit\Helper\Data;

/**
 * Class Account Bss.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Account extends AbstractAccount
{
    /**
     * @var \Bss\CompanyCredit\Helper\Data
     */
    private $helperData;

    /**
     * @param Context $context
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Get store credit tab content
     *
     * @return void
     */
    public function execute()
    {
        if ($this->helperData->isEnableModule()) {
            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->set(__('My Company Credit'));
            $this->_view->renderLayout();
        }
    }
}
