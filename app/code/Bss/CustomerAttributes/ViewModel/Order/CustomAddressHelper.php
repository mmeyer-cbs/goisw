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
namespace Bss\CustomerAttributes\ViewModel\Order;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CustomAddressHelper implements ArgumentInterface
{
    /**
     * @var Customerattribute
     */
    private $attributeHelper;
    /**
     * @var Json
     */
    private $json;

    /**
     * Info constructor.
     * @param Customerattribute $attributeHelper
     * @param Json $json
     */
    public function __construct(
        Customerattribute $attributeHelper,
        Json $json
    ) {
        $this->attributeHelper = $attributeHelper;
        $this->json = $json;
    }

    /**
     * @return Customerattribute
     */
    public function getAttributeHelper(): Customerattribute
    {
        return $this->attributeHelper;
    }

    /**
     * @return Json
     */
    public function getJson(): Json
    {
        return $this->json;
    }
}
