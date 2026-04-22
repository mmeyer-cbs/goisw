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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);
namespace Bss\CustomerAttributes\Api;

use Bss\CustomerAttributes\Api\Data\AttributeInterface;

interface AttributeRepositoryInterface
{
    /**
     * Create Model
     *
     * @return AttributeInterface
     */
    public function create();

    /**
     * Get dependent data by id
     *
     * @param int $id
     * @return AttributeInterface
     */
    public function load($id);

    /**
     * Get dependent data by attr_id
     *
     * @param int $attrId
     * @return AttributeInterface
     */
    public function getDataByAttrID($attrId);
}
