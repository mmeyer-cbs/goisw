<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bss\CustomerAttributes\Plugin\Customer\Controller\Adminhtml\Address;

use Bss\CustomerAttributes\Helper\CustomerAddress;
use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Backend\App\Action;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class for saving of customer address
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Save extends \Magento\Customer\Controller\Adminhtml\Address\Save
{
    public const CUSTOMER_ADDRESS = 'customer_address';

    protected $customerAddress;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var Customerattribute
     */
    protected $helper;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressDataFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        CustomerAddress             $customerAddress,
        Customerattribute           $helper,
        Action\Context              $context,
        AddressRepositoryInterface  $addressRepository,
        FormFactory                 $formFactory,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper            $dataObjectHelper,
        AddressInterfaceFactory     $addressDataFactory,
        LoggerInterface             $logger,
        JsonFactory                 $resultJsonFactory
    ) {
        $this->customerAddress = $customerAddress;
        $this->addressRepository = $addressRepository;
        $this->formFactory = $formFactory;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressDataFactory = $addressDataFactory;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct(
            $context,
            $addressRepository,
            $formFactory,
            $customerRepository,
            $dataObjectHelper,
            $addressDataFactory,
            $logger,
            $resultJsonFactory
        );
        $this->helper = $helper;
    }

    /**
     * Save customer address action
     * @param \Magento\Customer\Controller\Adminhtml\Address\Save $subject
     * @param \Closure $proceed
     *
     * @return Json
     */
    public function aroundExecute($subject, $proceed)
    {
        $customerId = $this->getRequest()->getParam('parent_id', false);
        $addressId = $this->getRequest()->getParam('entity_id', false);

        $error = false;
        try {
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->customerRepository->getById($customerId);

            $addressForm = $this->formFactory->create(
                'customer_address',
                'adminhtml_customer_address',
                [],
                false,
                false
            );

            $addressData = $addressForm->extractData($this->getRequest());
            $addressData = $addressForm->compactData($addressData);

            $addressData['region'] = [
                'region' => $addressData['region'] ?? null,
                'region_id' => $addressData['region_id'] ?? null,
            ];
            $addressToSave = $this->addressDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $addressToSave,
                $addressData,
                AddressInterface::class
            );
            $addressToSave->setCustomerId($customer->getId());
            $addressToSave->setIsDefaultBilling(
                (bool)$this->getRequest()->getParam('default_billing', false)
            );
            $addressToSave->setIsDefaultShipping(
                (bool)$this->getRequest()->getParam('default_shipping', false)
            );
            if ($addressId) {
                $addressToSave->setId($addressId);
                $message = __('Customer address has been updated.');
            } else {
                $addressToSave->setId(null);
                $message = __('New customer address has been added.');
            }
            $savedAddress = $this->addressRepository->save($addressToSave);
            $addressId = $savedAddress->getId();
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e);
            $error = true;
            $message = __('There is no customer with such id.');
        } catch (LocalizedException $e) {
            $error = true;
            $message = __($e->getMessage());
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $error = true;
            $message = __('We can\'t change customer address right now.');
            $this->logger->critical($e);
        }

        $addressId = empty($addressId) ? null : $addressId;
        $resultJson = $this->resultJsonFactory->create();
        $attributeAddress = $this->helper->converAddressCollectioin();
        if (isset($addressData)) {
            $resultJson->setData(
                [
                    'messages' => $message,
                    'error' => $error,
                    'data' => [
                        'entity_id' => $addressId
                    ],
                    'custom_attributes_address' => $this->customerAddress
                        ->getDataCustomAddress($addressData, $attributeAddress)
                ]
            );
        }
        return $resultJson;
    }
}
