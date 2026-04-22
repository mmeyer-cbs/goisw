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
 * @package    Bss_FastOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FastOrder\Controller\Mini;

use Magento\Framework\App\Action\Context;

/**
 * Class Form
 *
 * @package Bss\FastOrder\Controller\Sticky
 */
class Form extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Form constructor.
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->layout = $layout;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $html = $this->getContentPopup();
        return $resultJson->setData($html);
    }

    /**
     * @return mixed
     */
    protected function getContentPopup()
    {
        $template = 'Bss_FastOrder::mini-fast-order.phtml';
        $html = $this->layout
            ->createBlock(\Bss\FastOrder\Block\FastOrder::class)
            ->setTemplate($template);
        return $html->toHtml();
    }
}
