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

namespace Bss\CustomerAttributes\Controller\Adminhtml\Attribute;

use Bss\CustomerAttributes\Helper\Customer\Grid\NotDisplay;
use Bss\CustomerAttributes\Model\AttributeDependent;
use Bss\CustomerAttributes\Model\AttributeDependentRepository;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Model\Product\Url;
use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\Entity;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Controller\Adminhtml\Attribute
 */
class Edit extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\AbstractAction
{
    /**
     * @var Attribute
     */
    protected $model;

    /**
     * @var  $attributeDependent
     */
    protected $attributeDependent;

    /**
     * @var NotDisplay
     */
    protected $getAttributes;

    /**
     * @var AttributeDependentRepository
     */
    protected $attributeDependentRepository;

    /**
     * Edit constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Attribute $model
     * @param Url $productUrl
     * @param Entity $eavEntity
     * @param Registry $coreRegistry
     * @param AttributeDependentRepository $attributeDependentRepository
     * @param NotDisplay $getAttributes
     */
    public function __construct(
        Context                      $context,
        PageFactory                  $resultPageFactory,
        Attribute                    $model,
        Url                          $productUrl,
        Entity                       $eavEntity,
        Registry                     $coreRegistry,
        AttributeDependentRepository $attributeDependentRepository,
        NotDisplay                   $getAttributes
    ) {
        $this->model = $model;
        $this->attributeDependentRepository = $attributeDependentRepository;
        $this->getAttributes = $getAttributes;
        parent::__construct($context, $coreRegistry, $productUrl, $eavEntity, $resultPageFactory);
    }

    /**
     * Init actions
     *
     * @return \Magento\Framework\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Bss_CustomerAttributes::customer_attributes')
            ->addBreadcrumb(__('Customer Attributes'), __('Customer Attributes'))
            ->addBreadcrumb(__('Manage Customer Attributes'), __('Manage Customer Attributes'));

        return $resultPage;
    }

    /**
     * Edit Page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $attrId = $this->editPage();
        $item = $this->getItem($attrId);
        $resultPage = $this->createActionPage($item);
        $resultPage->getConfig()->getTitle()->prepend($attrId ? $this->model->getName() : __('New Customer Attribute'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js');
        return $resultPage;
    }

    /**
     * Doing Edit Page
     *
     * @return int
     */
    protected function editPage()
    {
        $attrId = $this->getRequest()->getParam('attribute_id');

        $this->model->setEntityTypeId($this->_entityTypeId);

        if ($attrId) {
            $this->model->load($attrId);
            if (!$this->model->getId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('customerattribute/*/');
            }

            // entity type check
            if ($this->model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be edited.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('customerattribute/*/');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getAttributeData(true);

        if (!empty($data)) {
            $this->model->addData($data);
        }

        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $attrId === null) {
            $this->model->addData($attributeData);
        }
        $validateRules = $this->model->getValidateRules();
        if (!empty($validateRules)) {
            $this->model->addData($validateRules);
        }
        $this->_coreRegistry->register('entity_attribute', $this->model);

        return $attrId;
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
            return __('Edit Customer Attribute');
        } else {
            return __('New Customer Attribute');
        }
    }

    /**
     * Prepare Tittle
     *
     * @param Phrase|null $title
     * @return Page
     */
    protected function createActionPage($title = null)
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Customer'), __('Customer'))
            ->addBreadcrumb(__('Manage Customer Attributes'), __('Manage Customer Attributes'))
            ->setActiveMenu('Magento_Customer::customer');
        if (!empty($title)) {
            $resultPage->addBreadcrumb($title, $title);
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Attributes'));
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
     * @return AttributeDependent
     */
    public function getCollection()
    {
        $attrId = $this->getRequest()->getParam('attribute_id');
        return $this->attributeDependentRepository->getDataByAttrID($attrId);
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
