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
namespace Bss\CustomerAttributes\Controller\Checkout;


use Bss\CustomerAttributes\Helper\Customerattribute;
use Bss\CustomerAttributes\Helper\GetHtmltoEmail;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class Billing
 * @SuppressWarnings(PHPMD)
 */
class Billing extends \Magento\Multishipping\Controller\Checkout\Billing
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var Customerattribute
     */
    private $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Json $json
     * @param QuoteRepository $quoteRepository
     * @param Customerattribute $helper
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        AddressRepositoryInterface $addressRepository,
        Json $json,
        QuoteRepository $quoteRepository,
        Customerattribute $helper,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
        $this->addressRepository = $addressRepository;
        $this->quoteRepository = $quoteRepository;
        $this->json = $json;
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Validation of selecting of billing address
     *
     * @return boolean
     */
    protected function _validateBilling()
    {
        if (!$this->_getCheckout()->getQuote()->getBillingAddress()->getFirstname()) {
            $this->_redirect('*/checkout_address/selectBilling');
            return false;
        }
        $quote = $this->_getCheckout()->getQuote();
        $customerAddressId = $quote->getBillingAddress()->getCustomerAddressId();
        $addresses = $this->addressRepository
            ->getById($customerAddressId)->getCustomAttributes();
        $customAddressAttribute = [];
        foreach ($addresses as $attributeCode => $attribute) {
            $addressAttribute = $this->attributeRepository
                ->get('customer_address', $attributeCode);
            $addressValue = $this->helper->getValueAddressAttributeForOrder(
                $addressAttribute,
                $attribute->getValue()
            );
            $value = [
                'value' => $addressValue,
                'label' => $addressAttribute->getFrontendLabel()
            ];
            $customAddressAttribute[$attributeCode] = $value;
        }
        $jsonAddress = $this->json->serialize($customAddressAttribute);
        $quote->getBillingAddress()->setCustomerAddressAttribute($jsonAddress);
        $quote->setDataChanges(true);
        $this->quoteRepository->save($quote);
        return true;
    }
}
