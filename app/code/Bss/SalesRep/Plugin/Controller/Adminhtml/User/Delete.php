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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Plugin\Controller\Adminhtml\User;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Config;

/**
 * Class Delete
 *
 * @package Bss\SalesRep\Plugin\Controller\Adminhtml\User
 */
class Delete
{
    /**
     * @var \Bss\SalesRep\Helper\Data
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * Delete constructor.
     * @param Config $config
     * @param TypeListInterface $typeList
     * @param \Bss\SalesRep\Helper\Data $helper
     */
    public function __construct(
        Config $config,
        TypeListInterface $typeList,
        \Bss\SalesRep\Helper\Data $helper
    ) {
        $this->typeList = $typeList;
        $this->config = $config;
        $this->helper = $helper;
    }

    /**
     * Clear cache after delete user
     *
     * @param string $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result)
    {
        if ($this->helper->isEnable()) {
            if ($this->config->isEnabled()) {
                $this->typeList->invalidate(
                    \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
                );
            }
        }
        return $result;
    }
}
