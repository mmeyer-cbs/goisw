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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Paymentshipping\Controller\Adminhtml\Paymentshipping;

use Bss\Paymentshipping\Model\PaymentshippingFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var array
     */
    protected $_availableTypes = ['payment', 'shipping'];

    /**
     * @var string
     */
    protected $messageManager;

    /**
     * @var \Bss\Paymentshipping\Model\PaymentshippingFactory
     */
    protected $paymentShipping;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param PaymentshippingFactory $paymentShipping
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Bss\Paymentshipping\Model\PaymentshippingFactory $paymentShipping,
        PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->paymentShipping = $paymentShipping;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $type = $this->getRequest()->getParam('type');
        if (!in_array($type, $this->_availableTypes)) {
            $this->messageManager->addErrorMessage(__('Unable to save. Wrong type specified.'));
            $this->_redirect('*/*', ['type' => 'payment', '_current' => true]);
        }
        $websiteId = $this->getRequest()->getParam('website_id');
        if (!$websiteId) {
            $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
        }
        $methods = $this->getRequest()->getPost('bssmethods');
        $methodCodes = $this->getRequest()->getPost('bssmethods_codes');

        try {
            foreach ($methodCodes as $methodCode) {
                $groups = $methods[$methodCode] ?? [];
                $visibilitys = $this->paymentShipping->create()->getCollection();
                $visibilitys->addFieldToFilter('type', ['eq' => $type]);
                $visibilitys->addFieldToFilter('website_id', ['eq' => $websiteId]);
                $visibilitys->addFieldToFilter('method', ['eq' => $methodCode]);
                if ($visibilitys->getSize() > 0) {
                    $firstItem = $this->returnLastItem($visibilitys);
                    $id = ($firstItem) ? $firstItem->getEntityId() : null;
                    $modelUpdate = $this->loadModel($id);
                    $modelUpdate->setGroupIds(implode(',', $groups));
                    $this->saveModel($modelUpdate);

                } else {
                    $modelInsert = $this->loadModel();
                    $modelInsert->setType($type);
                    $modelInsert->setWebsiteId($websiteId);
                    $modelInsert->setMethod($methodCode);
                    $modelInsert->setGroupIds(implode(',', $groups));
                    $this->saveModel($modelInsert);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        $message = __('%1 options have been saved.', $type);
        $this->messageManager->addSuccessMessage($message);

        $path = '*/*/' . $type . '/website_id/' . $websiteId;
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }

    /**
     * @param Collection $visibilitys
     * @return mixed
     */
    protected function returnLastItem($visibilitys)
    {
        return $visibilitys->getLastItem();
    }

    /**
     * @param int $id
     * @return object Model
     */
    protected function loadModel($id = null)
    {
        return $this->paymentShipping->create()->load($id);
    }

    /**
     * @param object $model
     * @return mixed this
     */
    protected function saveModel($model)
    {
        $model->save();
        return $this;
    }
}
