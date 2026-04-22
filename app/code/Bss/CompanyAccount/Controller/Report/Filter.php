<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Controller\Report;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Index
 *
 * @package Bss\CompanyAccount\Controller\Report
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Filter extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context         $context,
        CurrentCustomer $currentCustomer,
        JsonFactory     $resultJsonFactory
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Function get collection filter data
     *
     * @return void|\Magento\Framework\View\Element\BlockInterface
     */
    public function getCollectionFilter()
    {
        return $this->_view->getLayout()->createBlock('Bss\CompanyAccount\Block\Report\Filter')
            ->setTemplate('Bss_CompanyAccount::report/grid_filter.phtml')
            ->toHtml();
    }

    /**
     * Function execute Filter
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultJsonFactory->create()->setData(['output' => $this->getCollectionFilter()]);
    }
}
