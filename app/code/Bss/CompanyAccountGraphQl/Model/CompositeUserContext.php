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

namespace Bss\CompanyAccountGraphQl\Model;

use Magento\Authorization\Model\CompositeUserContext as CoreCompositeUserContext;

/**
 * Class CompositeUserContext for sub-user context
 */
class CompositeUserContext extends CoreCompositeUserContext implements UserContextInterface
{
    /**
     * Identify current sub-user ID.
     *
     * @return int|null
     */
    public function getSubUserId(): ?int
    {
        if ($this->getUserContext() && method_exists($this->getUserContext(), "getSubUserId")) {
            return $this->getUserContext()->getSubUserId();
        }

        return null;
    }
}
