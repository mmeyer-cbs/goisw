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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReorderProduct\Controller\Index;

use Magento\Framework\App\Action\Context;

/**
 * Class Updatecart
 *
 * @package Bss\ReorderProduct\Controller\Index
 */
class Updatecart extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\ReorderProduct\Helper\HelperClass
     */
    protected $helperClass;

    /**
     * Updatecart constructor.
     * @param Context $context
     * @param \Bss\ReorderProduct\Helper\HelperClass $helperClass
     */
    public function __construct(
        Context $context,
        \Bss\ReorderProduct\Helper\HelperClass $helperClass
    ) {
        $this->helperClass = $helperClass;
        parent::__construct($context);
    }

    /**
     * Update cart redirect
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_redirect('/');
            return;
        }

        $resultJson = $this->helperClass->returnResultJsonFactory()->create();
        $resultJson->setHeader('Content-type', 'application/json');
        $resultJson->setData(['result' => true]);
        return $resultJson;
    }
}
