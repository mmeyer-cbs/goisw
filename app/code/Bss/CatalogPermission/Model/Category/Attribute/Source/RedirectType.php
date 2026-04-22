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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Model\Category\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class CustomSource
 *
 * @package Bss\CatalogPermission\Model\Category\Attribute\Source
 */
class RedirectType extends AbstractSource
{
    const USE_GLOBAL = 1;
    const CUSTOMIZE_PER_PAGE = 2;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * RedirectType constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Get all customer group
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->request->getParam('page_id')) {
            return [
                ['value' => self::USE_GLOBAL, 'label' => __('Use Global Config')],
                ['value' => self::CUSTOMIZE_PER_PAGE, 'label' => __('Customize Per Page')],
            ];
        }
        return [
            ['value' => self::USE_GLOBAL, 'label' => __('Use Global Config')],
            ['value' => self::CUSTOMIZE_PER_PAGE, 'label' => __('Customize Per Category')],
        ];
    }
}
