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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Model;

use Magento\Customer\Api\AccountDelegationInterface;
use Magento\Framework\Controller\Result\Redirect;
use Bss\QuoteExtension\Api\QuoteExtensionCustomerDelegateInterface;
use Magento\Sales\Observer\AssignOrderToCustomerObserver;

/**
 * {@inheritdoc}
 *
 * @see AssignOrderToCustomerObserver
 */
class QuoteExtensionCustomerDelegate implements QuoteExtensionCustomerDelegateInterface
{
    /**
     * @var QuoteExtensionCustomerExtractor
     */
    private $customerExtractor;

    /**
     * @var AccountDelegationInterface
     */
    private $delegateService;

    /**
     * @param QuoteExtensionCustomerExtractor $customerExtractor
     * @param AccountDelegationInterface $delegateService
     */
    public function __construct(
        QuoteExtensionCustomerExtractor $customerExtractor,
        AccountDelegationInterface $delegateService
    ) {
        $this->customerExtractor = $customerExtractor;
        $this->delegateService = $delegateService;
    }

    /**
     * {@inheritdoc}
     */
    public function delegateNew(int $quoteId, $quoteExtensionId)
    {
        return $this->delegateService->createRedirectForNew(
            $this->customerExtractor->extract($quoteId),
            ['__quoteextension_assign_quote_id' => $quoteId,
                '__quoteextension_assign_quoteextension_id' => $quoteExtensionId]
        );
    }
}
