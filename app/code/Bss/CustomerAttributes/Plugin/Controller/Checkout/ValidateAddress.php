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
namespace Bss\CustomerAttributes\Plugin\Controller\Checkout;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Exception;
use Magento\Checkout\Controller\Onepage;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class ValidateAddress
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    private $_actionFlag;
    /**
     * @var Customerattribute
     */
    private $helper;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ValidateAddress constructor.
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param Context $context
     * @param Customerattribute $helper
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        Context $context,
        Customerattribute $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        LoggerInterface $loggerInterface
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->attributeRepository = $attributeRepository;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->messageManager = $context->getMessageManager();
        $this->_actionFlag = $context->getActionFlag();
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->logger = $loggerInterface;
    }

    /**
     * @param Onepage $subject
     * @param $result
     * @throws LocalizedException
     */
    public function afterDispatch(Onepage $subject, $result)
    {
        $resultValidate = $this->preDispatchValidateCustomer();
        if ($resultValidate instanceof \Magento\Framework\Controller\ResultInterface) {
            return $resultValidate;
        }
        return $result;
    }

    /**
     * @return bool|Redirect
     * @codeCoverageIgnore
     * @throws LocalizedException
     */
    protected function preDispatchValidateCustomer()
    {
        try {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
        } catch (NoSuchEntityException $e) {
            return true;
        }
        $actionUrl = [];
        if ($this->redirect->getRefererUrl()){
            $refererUrl = trim($this->redirect->getRefererUrl(),'/');
            $actionUrl = explode("/", $refererUrl);
        }
        if (end($actionUrl) == 'checkout'){
            return true;
        }
        if (isset($customer)) {
            $addressId = $customer->getDefaultShipping();
            if ($addressId) {
                $validationResult = $this->validateAddress($addressId);
                if (!empty($validationResult)) {
                    foreach ($validationResult as $error) {
                        $this->messageManager->addErrorMessage($error);
                    }
                    $this->_actionFlag->set('', 'no-dispatch', true);
                    $redirectPath = 'customer/address';
                    return $this->resultRedirectFactory->create()->setPath($redirectPath);
                }
            }
        }
        return true;
    }

    /**
     * @param $addressId
     * @return array|false
     * @throws LocalizedException
     * @codeCoverageIgnore
     */
    public function validateAddress($addressId)
    {
        try {
            $customerAddress = $this->addressRepository->getById($addressId);
        } catch (Exception $exception) {
            $this->logger->critical(__($exception->getMessage()));
            return false;
        }

        $addressAttributeCollection = $this->helper->getAddressCollection();
        if ($addressAttributeCollection->getSize() > 0) {
            $validationResult = [];
            foreach ($addressAttributeCollection as $item) {
                if ($this->helper->isAddressShowInBook($item->getAttributeCode()) &&
                    $this->helper->isVisible($item->getAttributeCode())) {
                    $value = $customerAddress->getCustomAttribute($item->getAttributeCode());
                    $result = $this->validate($item, $value);
                    if ($result) {
                        $validationResult[] = $result;
                    }
                }
            }
            return $validationResult;
        }
        return false;
    }

    /**
     * Validate object
     *
     * @param $attribute
     * @param $value
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validate($attribute, $value)
    {
        if ($attribute->getIsVisible()
            && $attribute->getIsRequired()
            && $attribute->isValueEmpty($value)
            && $attribute->isValueEmpty($attribute->getDefaultValue())
        ) {
            $label = $attribute->getFrontend()->getLabel();
            return 'The ' . $label . ' address attribute value is empty. Set the attribute and try again.';
        }
        return false;
    }
}
