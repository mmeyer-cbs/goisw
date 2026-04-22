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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Bss\CustomerAttributes\Helper\SaveObject;
use Bss\CustomerAttributes\Model\AddressAttributeDependentRepository;
use Bss\CustomerAttributes\Model\AttributeDependentRepository;
use Bss\CustomerAttributes\Model\HandleData;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Model\Product\Url;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\Entity;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD)
 */
class Save extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Save
{

    /**
     * @var SaveObject
     */
    protected $saveObject;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Customerattribute
     */
    protected $helperCustomerAttribute;

    /**
     * @var AttributeDependentRepository
     */
    protected $attributeModel;
    /**
     * @var AddressAttributeDependentRepository
     */
    protected $addAttributeModel;
    /**
     * @var HandleData
     */
    protected $handleData;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Url $productUrl
     * @param Entity $eavEntity
     * @param PageFactory $resultPageFactory
     * @param SaveObject $saveObject
     * @param CustomerRepositoryInterface $customerRepository
     * @param Customerattribute $helperCustomerAttribute
     * @param \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Edit $edit
     * @param SerializerInterface $serializer
     * @param AddressAttributeDependentRepository $addAttributeModel
     * @param AttributeDependentRepository $attributeModel
     * @param HandleData $handleData
     */
    public function __construct(
        Context                                                     $context,
        Registry                                                    $coreRegistry,
        Url                                                         $productUrl,
        Entity                                                      $eavEntity,
        PageFactory                                                 $resultPageFactory,
        SaveObject                                                  $saveObject,
        CustomerRepositoryInterface                                 $customerRepository,
        Customerattribute                                           $helperCustomerAttribute,
        \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Edit $edit,
        SerializerInterface                                         $serializer,
        AddressAttributeDependentRepository                         $addAttributeModel,
        AttributeDependentRepository                                $attributeModel,
        HandleData                                                  $handleData
    ) {
        $this->attributeModel = $attributeModel;
        $this->addAttributeModel = $addAttributeModel;
        $this->handleData = $handleData;
        parent::__construct(
            $context,
            $coreRegistry,
            $productUrl,
            $eavEntity,
            $resultPageFactory,
            $saveObject,
            $customerRepository,
            $helperCustomerAttribute,
            $edit,
            $serializer,
            $attributeModel,
            $addAttributeModel,
            $handleData
        );
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
     * Save Attribute Execute
     *
     * @return bool|Redirect|ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $attributeIdRequest = null;
        if ($data) {
            $entityType = 'customer_address';
            $this->saveData($data, $entityType, $attributeIdRequest);
        }
        $saveData = $this->serializer->serialize($this->getDependentDataAddress());
        if ($this->getRequest()->getParam('back')) {
            if ($this->validateDependentData()) {
                $this->saveDependentData($saveData);
            }
            return $this->returnResult(
                'addressattribute/*/edit',
                ['attribute_id' => $attributeIdRequest, '_current' => true]
            );
        }
        return $this->returnResult('addressattribute/*/', [], ['error' => true]);
    }

    /**
     * Validate Address Dependent Data
     *
     * @return bool
     * @throws \Exception
     */
    public function validateAddressDependentData()
    {
        $arrDependentsData = $this->getRequest()->getParam('relation_data');
        return $this->handleData->validateDependentDataBE($arrDependentsData);
    }

    /**
     * Get Dependent Data
     *
     * @return false|string
     */
    public function getDependentDataAddress()
    {
        $dependentsDataArray = $this->getRequest()->getParam('relation_data');
        return $this->handleData->getDependentDataBE($dependentsDataArray);
    }

    /**
     * Save Dependent Data
     *
     * @param string|mixed $dependentsData
     * @throws \Exception
     */
    public function saveDependentData($dependentsData)
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $model = $this->addAttributeModel;
        $this->handleData->saveDependentDataBE($dependentsData, $attributeId, $model);
    }

    /**
     * Set Use In Form
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getUsedInForm($data)
    {
        $usedInForms = $this->saveObject->returnDataObjectFactory()->create();

        $usedInForms[0] = 'adminhtml_customer';
        $usedInForms[1] = 'customer_register_address';
        $usedInForms[2] = 'customer_address';
        $usedInForms[3] = 'is_customer_attribute';
        $num = 4;
        if ($this->setUseInFormAdminCheckout($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormAdminCheckout($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUseInFormCheckout($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormCheckout($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUseInAddressBook($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInAddressBook($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInFormOrderDetail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormOrderDetail($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInFormAdminOrderDetail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormAdminOrderDetail($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInOrderEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInOrderEmail($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInInvoiceEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInInvoiceEmail($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInShippingEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInShippingEmail($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUseInMemoEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInMemoEmail($data, $usedInForms, $num);
        }
        return $usedInForms;
    }

    /**
     * Set Attribute use in Admin Checkout page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormAdminCheckout($data, $usedInForms, $num)
    {
        if (isset($data['adminhtml_customer_address']) && $data['adminhtml_customer_address'] == 1) {
            $usedInForms[$num] = 'adminhtml_customer_address';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Checkout Page page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormCheckout($data, $usedInForms, $num)
    {
        if (isset($data['show_checkout_frontend']) && $data['show_checkout_frontend'] == 1) {
            $usedInForms[$num] = 'show_checkout_frontend';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Address Book page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */

    private function setUseInAddressBook($data, $usedInForms, $num)
    {
        if (isset($data['customer_address_edit']) && $data['customer_address_edit'] == 1) {
            $usedInForms[$num] = 'customer_address_edit';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Order Details
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormOrderDetail($data, $usedInForms, $num)
    {
        if (isset($data['order_detail']) && $data['order_detail'] == 1) {
            $usedInForms[$num] = 'order_detail';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Admin Order Details
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormAdminOrderDetail($data, $usedInForms, $num)
    {
        if (isset($data['adminhtml_order_detail']) && $data['adminhtml_order_detail'] == 1) {
            $usedInForms[$num] = 'adminhtml_order_detail';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Order email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInOrderEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_order_email']) && $data['show_in_order_email'] == 1) {
            $usedInForms[$num] = 'show_in_order_email';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Invoice Email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInInvoiceEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_invoice_email']) && $data['show_in_invoice_email'] == 1) {
            $usedInForms[$num] = 'show_in_invoice_email';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Invoice Email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInShippingEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_shipping_email']) && $data['show_in_shipping_email'] == 1) {
            $usedInForms[$num] = 'show_in_shipping_email';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Credit memo Email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInMemoEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_credit_memo_email']) && $data['show_in_credit_memo_email'] == 1) {
            $usedInForms[$num] = 'show_in_credit_memo_email';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Check permission via ACL resource
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::save');
    }
}
