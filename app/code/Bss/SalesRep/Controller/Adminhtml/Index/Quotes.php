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

use Bss\SalesRep\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Order
 *
 * @package Bss\SalesRep\Controller\Adminhtml\Index
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Quotes extends Order
{
    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * Quotes constructor.
     *
     * @param Manager $moduleManager
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Data $helper
     */
    public function __construct(
        Manager $moduleManager,
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Data $helper
    ) {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $resultPageFactory, $resultForwardFactory, $helper);
    }

    /**
     * Report Page
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $saleRepEnable = $this->helper->isEnable();
        $quoteEnable = $this->moduleManager->isEnabled('Bss_QuoteExtension');
        if ($quoteEnable) {
            if ($saleRepEnable) {
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend((__('Quote')));
                return $resultPage;
            }
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('no-route');
            return $resultForward;
        }
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('quote');
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
