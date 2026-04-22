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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Controller\Index;

use Bss\CatalogPermission\Helper\ModuleConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Bss\CatalogPermission\Helper\Data;

/**
 * Class Index
 *
 * @package Bss\CatalogPermission\Controller\Index
 */
class Index extends Action
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var Data $helper
     */
    protected $helper;

    /**
     * @var \Bss\CatalogPermission\Helper\CheckPermission
     */
    protected $checkPermission;

    /**
     * Index constructor.
     * @param Context $context
     * @param ModuleConfig $moduleConfig
     * @param Data $helper
     * @param \Bss\CatalogPermission\Helper\CheckPermission $checkPermission
     */
    public function __construct(
        Context $context,
        ModuleConfig $moduleConfig,
        Data $helper,
        \Bss\CatalogPermission\Helper\CheckPermission $checkPermission
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->helper = $helper;
        $this->checkPermission = $checkPermission;
        parent::__construct($context);
    }

    /**
     * Controller Execute
     *
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $pageType = $this->getRequest()->getParam('pagetype');
        $pageIdParam = $this->getRequest()->getParam('pageid');
        $customUrlParam = $this->getRequest()->getParam('customurl');
        $referentUrl = $this->getRequest()->getParam('referent');
        $errorMessage = $this->getRequest()->getParam('message');
        $resultRedirect = $this->resultRedirectFactory->create();
        $message = $this->moduleConfig->getErrorMessage();
        $pageId = $this->moduleConfig->getPageIdToRedirect();
        $customUrl = $this->moduleConfig->getCustomCatalogUrl();
        if ($pageType == 'category' || $pageType == 'product') {
            if ($pageIdParam) {
                if ($customUrlParam && $this->checkPermission->checkCustomUrl($customUrlParam)) {
                    $urlPage = 'no-route';
                } else {
                    $urlPage = $this->helper->getRedirectUrl($pageIdParam, $customUrlParam, $referentUrl);
                    $message = $errorMessage;
                }
            } else {
                if ($customUrl && $this->checkPermission->checkCustomUrl($customUrl)) {
                    $urlPage = 'no-route';
                } else {
                    $urlPage = $this->helper->getRedirectUrl($pageId, $customUrl, $referentUrl);
                }
            }
            if ($message != null) {
                $this->messageManager->addErrorMessage($message);
            }
            $resultRedirect->setPath($urlPage);
            return $resultRedirect;
        }
        return $resultRedirect->setPath('');
    }
}
