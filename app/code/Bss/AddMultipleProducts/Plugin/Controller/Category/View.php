<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     Bss_AddMultipleProducts
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AddMultipleProducts\Plugin\Controller\Category;

use Bss\AddMultipleProducts\Helper\Data;

/**
 * Class View
 */
class View
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * View constructor.
     *
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param \Magento\Catalog\Controller\Category\View $action
     * @param \Magento\Framework\View\Result\Page $page
     * @return \Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(\Magento\Catalog\Controller\Category\View $action, $page)
    {
        if ($this->helper->isModuleOutputEnabled('Mageplaza_AjaxLayer') &&
            $action->getRequest()->isAjax()
        ) {
            if ($this->helper->isMageplazaAjaxEnabled()) {
                $page->getLayout()->getBlock('category.products')->setIsAjaxLayerEnabled(true);
            }
        }
        return $page;
    }
}
