<?php
declare(strict_types=1);
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
 * @package    Bss_CustomPricingGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricingGraphQl\Plugin;

use Bss\CustomPricingGraphQl\Model\ApplyCustomPriceForCart;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ApplyCustomPrice
 * Apply custom price for product
 */
class ApplyCustomPricePlugin
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    protected $maskedQuoteIdToQuoteId;

    /**
     * @var ApplyCustomPriceForCart
     */
    protected $applyCustomPriceForCart;

    /**
     * ApplyCustomPrice constructor.
     *
     * @param LoggerInterface $logger
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param ApplyCustomPriceForCart $applyCustomPriceForCart
     */
    public function __construct(
        LoggerInterface $logger,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        ApplyCustomPriceForCart $applyCustomPriceForCart
    ) {
        $this->logger = $logger;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->applyCustomPriceForCart = $applyCustomPriceForCart;
    }

    /**
     * Set custom price for quote item
     *
     * @param \Magento\QuoteGraphQl\Model\Resolver\AddProductsToCart  $subject
     * @param array $result
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolve(
        $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $args = $this->initParams($args);
        $cartId = $this->maskedQuoteIdToQuoteId->execute($args['cartId']);
        try {
            $cart = $this->applyCustomPriceForCart->execute($cartId);
            $result['cart']['model'] = $cart;
        } catch (\Exception $e) {
            $this->logger->critical(
                __("BSS.ERROR: When apply custom price for cart. ") . $e
            );
        }

        return $result;
    }

    /**
     * Init params
     *
     * @param array $args
     * @return array
     */
    public function initParams($args)
    {
        if (!isset($args['cartId'])) {
            $args = $args['input'];
            $args['cartId'] = $args['cart_id'];
            $args['cartItems'] = $args['cart_items'];
        }
        return $args;
    }
}
