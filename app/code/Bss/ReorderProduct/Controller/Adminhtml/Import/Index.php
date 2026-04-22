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
namespace Bss\ReorderProduct\Controller\Adminhtml\Import;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @package Bss\ReorderProduct\Controller\Adminhtml\Import
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\ReorderProduct\Model\ResourceModel\ImportFactory
     */
    protected $importResourceFactory;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Bss\ReorderProduct\Model\ResourceModel\ImportFactory $importResourceFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\ReorderProduct\Model\ResourceModel\ImportFactory $importResourceFactory
    ) {
        parent::__construct($context);
        $this->importResourceFactory = $importResourceFactory;
    }

    /**
     * Import old order to reorder product table
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $modelImport = $this->importResourceFactory->create();
            $numberRowEachrun = 5000;
            $modelImport->clear();
            $totalRow = $modelImport->getNumberItem();
            $count = ceil($totalRow/$numberRowEachrun);
            for ($i=0; $i < $count; $i++) {
                $start = $i*$numberRowEachrun;
                $modelImport->import($start, $numberRowEachrun);
                $totalRowImport = $start + $numberRowEachrun;
                if ($totalRowImport >= $totalRow) {
                    $this->messageManager->addSuccessMessage('Import Success!');
                }
            }
        } catch (\LogicException $exception) {
            $this->messageManager->addErrorMessage('There was an error in the process of importing orders');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
