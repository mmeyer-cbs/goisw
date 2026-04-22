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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute;

use Bss\CustomerAttributes\Model\ResourceModel\AddressAttribute\Grid\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Attribute;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute
 */
class MassDelete extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\MassDelete
{
    /**
     * @var CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $addressCollectionFactory
     * @param \Bss\CustomerAttributes\Model\ResourceModel\Attribute\Grid\CollectionFactory $collectionFactory
     * @param Attribute $model
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $addressCollectionFactory,
        \Bss\CustomerAttributes\Model\ResourceModel\Attribute\Grid\CollectionFactory $collectionFactory,
        Attribute $model
    ) {
        $this->addressCollectionFactory = $addressCollectionFactory;
        parent::__construct($context, $filter, $collectionFactory, $model);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->addressCollectionFactory->create());
        $recordDeleted = 0;
        foreach ($collection->getItems() as $auctionProduct) {
            $this->deleteAttribute($auctionProduct);
            $recordDeleted++;
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been deleted.', $recordDeleted)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /*
 * Check permission via ACL resource
 */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::customer_attributes_delete');
    }
}
