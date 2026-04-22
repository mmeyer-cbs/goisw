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
namespace Bss\QuoteExtension\Model;

use Magento\Framework\App\Helper\Context;

/**
 * Class Label
 */
class Label
{
    /**
     * @var \Bss\QuoteExtension\Model\Config\Source\Quotable
     */
    protected $quotable;

    public function __construct(
        \Bss\QuoteExtension\Model\Config\Source\Quotable $quotable
    ) {
        $this->quotable = $quotable;
    }

    /**
     * Get label from config quotable
     *
     * @param $configQuotable
     * @return mixed|null
     */
    public function getLabelQuotable($configQuotable)
    {
        $options = $this->quotable->toArray();
        if (isset($options[$configQuotable])) {
            return $options[$configQuotable];
        }
        return null;
    }

}
