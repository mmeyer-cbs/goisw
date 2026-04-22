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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccountGraphQl\Model\Authorization\TokenSubUserContext;

/**
 * Class Token
 * For create sub-user token
 * @method Token setSubUserId(int $subId)
 * @method int getSubUserId()
 */
class Token extends \Magento\Integration\Model\Oauth\Token
{
    /**
     * Create sub-user token
     *
     * @param int $subId
     * @return $this
     */
    public function createSubUserToken($subId)
    {
        $this->setSubUserId($subId);
        return $this->saveAccessToken(TokenSubUserContext::USER_TYPE_SUB_USER);
    }
}
