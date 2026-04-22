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

use Bss\CustomerAttributes\Helper\Customer\Grid\NotDisplay;
use Bss\CustomerAttributes\Model\AddressAttributeDependent;
use Bss\CustomerAttributes\Model\AddressAttributeDependentRepository;
use Bss\CustomerAttributes\Model\AttributeDependentRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Url;
use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\Entity;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute
 */
class Edit extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Edit
{
    /**
     * @var AddressAttributeDependentRepository
     */
    protected $addressAttributeDependentRepository;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Attribute $model
     * @param Url $productUrl
     * @param Entity $eavEntity
     * @param Registry $coreRegistry
     * @param AttributeDependentRepository $attributeDependentRepository
     * @param AddressAttributeDependentRepository $addressAttributeDependentRepository
     * @param NotDisplay $getAttributes
     */
    public function __construct(
        Context                             $context,
        PageFactory                         $resultPageFactory,
        Attribute                           $model,
        Url                                 $productUrl,
        Entity                              $eavEntity,
        Registry                            $coreRegistry,
        AttributeDependentRepository        $attributeDependentRepository,
        AddressAttributeDependentRepository $addressAttributeDependentRepository,
        NotDisplay                          $getAttributes
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $model,
            $productUrl,
            $eavEntity,
            $coreRegistry,
            $attributeDependentRepository,
            $getAttributes
        );
        $this->addressAttributeDependentRepository = $addressAttributeDependentRepository;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_entityTypeId = $this->eavEntity
            ->setType('customer_address')->getTypeId();
        return Action::dispatch($request);
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Bss_CustomerAttributes::customer_attributes')
            ->addBreadcrumb(__('Customer Address Attributes'), __('Customer Address Attributes'))
            ->addBreadcrumb(__('Manage Customer Address Attributes'), __('Manage Customer Address Attributes'));

        return $resultPage;
    }

    /**
     * Edit Page
     *
     * @return ResultInterface
     */

    public function execute()
    {
        $attrId = $this->editPage();
        $item = $this->getItem($attrId);
        $resultPage = $this->createActionPage($item);
        $resultPage->getConfig()->getTitle()
            ->prepend($attrId ? $this->model->getName() : __('New Customer Address Attribute'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js');
        return $resultPage;
    }

    /**
     * Get Item
     *
     * @param int $attrId
     * @return Phrase
     */
    private function getItem($attrId)
    {
        if ($attrId) {
            return __('Edit Customer Address Attribute');
        } else {
            return __('New Customer Address Attribute');
        }
    }

    /**
     * Prepare Tittle
     *
     * @param Phrase|null $title
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createActionPage($title = null)
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Customer'), __('Customer'))
            ->addBreadcrumb(__('Manage Customer Address Attributes'), __('Manage Customer Address Attributes'))
            ->setActiveMenu('Magento_Customer::customer');
        if (!empty($title)) {
            $resultPage->addBreadcrumb($title, $title);
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Address Attributes'));
        return $resultPage;
    }

    /**
     * Check permission via ACL resource
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::customer_attributes_edit');
    }

    /**
     * Get Data
     *
     * @return AddressAttributeDependent
     */
    public function getCollection()
    {
        $attrId = $this->getRequest()->getParam('attribute_id');
        return $this->addressAttributeDependentRepository->getDataByAttrID($attrId);
    }

    /**
     * Get Attribute By Id
     *
     * @return array|AbstractDb|AbstractCollection|null
     */
    public function getAttributeId()
    {
        return $this->getRequest()->getParam('attribute_id');
    }
}
