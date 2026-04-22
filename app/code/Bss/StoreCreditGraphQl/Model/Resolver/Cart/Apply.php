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
 * @package    Bss_StoreCreditGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCreditGraphQl\Model\Resolver\Cart;

use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;


/**
 * Class Apply
 *
 * @package Bss\StoreCreditGraphQl\Model\Resolver\Cart
 */
class Apply implements ResolverInterface
{
    /**
     * @var StoreCreditRepositoryInterface
     */
    protected $storeCreditRepository;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    protected $helperData;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;


    /**
     * Apply constructor.
     *
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param \Bss\StoreCredit\Helper\Data $helperData
     * @param GetCartForUser $getCartForUser
     */
    public function __construct(
        \Bss\StoreCredit\Api\StoreCreditRepositoryInterface $storeCreditRepository,
        \Bss\StoreCredit\Helper\Data $helperData,
        GetCartForUser $getCartForUser
    ) {
        $this->storeCreditRepository = $storeCreditRepository;
        $this->helperData = $helperData;
        $this->getCartForUser = $getCartForUser;

    }

    /**
     * Apply store credit to cart
     *
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $websiteId = (int)$context->getExtensionAttributes()->getStore()->getWebsiteId();
        $this->validate($args, $context);
        $maskedCartId = $args['input']['cart_id'];
        $currentUserId = $context->getUserId();

        $credit = $this->storeCreditRepository->get($currentUserId, $websiteId);
        if( $credit->getBalanceAmount() < $args["input"]["amount"]) {
            return [
                "status" => false,
                "message" => (__('Make sure you don\'t apply store credit more than your credit.'))
            ];

        }
        $quote = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        $quote->setBaseBssStorecreditAmountInput($args["input"]["amount"]);
        $quote->collectTotals();
        $quote->save();
        return [
            "status" => true,
            "message" => (__('You applied store credit to cart.'))
        ];
    }

    /**
     * Validate input
     *
     * @param array $args
     * @param ContextInterface $context
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     */
    public function validate($args, $context)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $currentUserId = $context->getUserId();
        if (!$this->helperData->getGeneralConfig("checkout_page_display", $storeId)) {
            throw new GraphQlInputException(__('You cannot add "store credit" to the cart.'));
        }

        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (empty($args['input']['amount'])) {
            throw new GraphQlInputException(__('Required parameter "amount" is missing'));
        }

        if (empty($currentUserId)) {
            throw new GraphQlAuthorizationException(__('Please specify a valid customer'));
        }
    }
}
