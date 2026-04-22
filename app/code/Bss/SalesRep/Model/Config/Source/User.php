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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model\Config\Source;

class User extends \Bss\SalesRep\Model\Entity\Attribute\Source\SalesRepresentive
{
    /**
     * Get list options in array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = parent::getAllOptions();
        array_shift($res);
        return $res;
    }

}
