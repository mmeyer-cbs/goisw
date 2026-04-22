<?php
declare(strict_types = 1);

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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManager;

/**
 * Class GetType
 *
 * @package Bss\CompanyAccount\Helper
 */
class GetType extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * GetType constructor.
     *
     * @param StoreManager $storeManager
     * @param Context $context
     */
    public function __construct(
        StoreManager $storeManager,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get front end area
     *
     * @return string
     */
    public function getAreaFrontend()
    {
        return \Magento\Framework\App\Area::AREA_FRONTEND;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }
}
