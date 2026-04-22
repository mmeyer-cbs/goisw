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

namespace Bss\StoreCredit\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Json\Helper\Data;
use Bss\StoreCredit\Model\HistoryFactory;

/**
 * Class StoreCredit
 * @package Bss\StoreCredit\Controller\Adminhtml
 */
abstract class StoreCredit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_StoreCredit::storecredit';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * @var \Bss\StoreCredit\Model\HistoryFactory
     */
    public $historyFactory;

    /**
     * StoreCredit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        Data $jsonHelper,
        HistoryFactory $historyFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->historyFactory = $historyFactory;
    }

    /**
     * Initiate action
     *
     * @return this
     */
    public function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Bss_StoreCredit::storecredit')
            ->_addBreadcrumb(__('Store Credit'), __('Store Credit'));
        return $this;
    }
}
