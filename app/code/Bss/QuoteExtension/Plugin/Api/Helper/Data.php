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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Plugin\Api\Helper;

use Bss\QuoteExtension\Model\Api as ModelApi;

/**
 * Class Data
 */
class Data
{
    /**
     * @var ModelApi
     */
    protected $modelApi;

    /**
     * QueryProducts constructor.
     *
     * @param modelApi $modelApi
     */
    public function __construct(
        modelApi $modelApi
    ) {
        $this->modelApi = $modelApi;
    }

    /**
     * Set customer group id when use graphql
     *
     * @param \Bss\QuoteExtension\Helper\Data $subject
     * @param int $result
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomerGroupId($subject, int $result)
    {
        $customerId = $this->modelApi->getCustomerId();
        if ($customerId) {
            return $this->modelApi->getCustomerGroupId($customerId);
        }
        return $result;
    }

}
