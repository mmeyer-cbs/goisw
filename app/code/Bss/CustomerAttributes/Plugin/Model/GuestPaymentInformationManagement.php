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

namespace Bss\CustomerAttributes\Plugin\Model;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GuestPaymentInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;
    /**
     * @var Customerattribute
     */
    private $helper;

    /**
     * PaymentInformationManagement constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Customerattribute $helper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory      $quoteIdMaskFactory,
        Customerattribute       $helper
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Model\GuestPaymentInformationManagement $subject
     * @param $result
     * @param $cartId
     * @param $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return mixed
     */
    public function afterSavePaymentInformation(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
                                                                  $result,
                                                                  $cartId,
                                                                  $email,
        PaymentInterface                                          $paymentMethod,
        AddressInterface                                          $billingAddress = null
    )
    {
        if ($this->helper->isEnable()) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            /** @var Quote $quote */
            $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());

            if ($billingAddress) {
                $customerAddressField = $billingAddress->getExtensionAttributes()->getCustomField();
                if (!empty($customerAddressField)) {
                    $quote->getBillingAddress()->setData('customer_address_attribute', $customerAddressField);
                    $quote->setDataChanges(true);
                }
            }
        }
        return $result;
    }

}
