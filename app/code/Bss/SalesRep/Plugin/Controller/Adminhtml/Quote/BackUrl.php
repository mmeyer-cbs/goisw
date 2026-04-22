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
namespace Bss\SalesRep\Plugin\Controller\Adminhtml\Quote;

use Bss\SalesRep\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Class BackUrl
 *
 * @package Bss\SalesRep\Plugin\Controller\Adminhtml\Quote
 */
class BackUrl
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * BackUrl constructor.
     *
     * @param Data $helper
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Data $helper,
        RequestInterface $request,
        RedirectFactory $redirectFactory
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Change Back Url if User is SalesRep
     *
     * @param mixed $subject
     * @param mixed $result
     * @return Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        $subject,
        $result
    ) {
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep()) {
            $resultRedirect = $this->redirectFactory->create();
            return $resultRedirect->setPath('salesrep/index/quotes');
        }
        return $result;
    }
}
