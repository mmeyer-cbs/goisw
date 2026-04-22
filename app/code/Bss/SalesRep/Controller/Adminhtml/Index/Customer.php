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
namespace Bss\SalesRep\Controller\Adminhtml\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class Order
 *
 * @package Bss\SalesRep\Controller\Adminhtml\Index
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Customer extends Order
{
    /**
     * Customer page
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        if ($this->helper->isEnable()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend((__('Customer')));

            return $resultPage;
        }
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('no-route');
        return $resultForward;
    }

    /**
     * Check permission
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_SalesRep::salesrep');
    }
}
