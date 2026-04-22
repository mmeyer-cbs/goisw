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
 * @package    Bss_CompanyAccountGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccountGraphQl\Exception;

/**
 * Class Could not delete exception
 */
class GraphQlCouldNotDeleteException extends \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
{
    const EXCEPTION_CATEGORY = "graphql-could-not-delete";

    /**
     * @inheritDoc
     */
    public function getCategory() : string
    {
        return self::EXCEPTION_CATEGORY;
    }
}
