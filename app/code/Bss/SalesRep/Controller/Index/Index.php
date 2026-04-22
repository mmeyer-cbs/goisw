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
namespace Bss\SalesRep\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Bss\SalesRep\Controller\Index
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends AbstractAccount
{
    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * Order constructor.
     * @param AuthorizationInterface $authorization
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        AuthorizationInterface $authorization,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->authorization = $authorization;
    }

    /**
     * Manage Sales Rep Page
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }

    /**
     * Check permission
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->authorization->isAllowed('Bss_SalesRep::salesrep');
    }
}
