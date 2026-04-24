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

namespace Bss\CatalogPermission\Plugin\Block\Widget;

use Bss\CatalogPermission\Helper\Data;
use Bss\CatalogPermission\Helper\ModuleConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Link
 *
 * @package Bss\CatalogPermission\Plugin\Block\Widget
 */
class Link extends \Magento\Framework\View\Element\Html\Link implements BlockInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Bss\CatalogPermission\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helperData;

    /**
     * Link constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig
     * @param \Bss\CatalogPermission\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session          $customerSession,
        ModuleConfig     $moduleConfig,
        Data             $helperData,
        array            $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->moduleConfig = $moduleConfig;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Plugin after get link
     *
     * @param \Magento\Catalog\Block\Widget\Link $subject
     * @param string|false $result
     * @return string|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetHref($subject, $result)
    {
        $enableCatalogPermission = $this->moduleConfig->enableCatalogPermission();
        if (!$enableCatalogPermission) {
            return $result;
        }
        $disableCategoryLink = $this->moduleConfig->disableCategoryLink();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $listIdSubCategory = $this->helperData->getIdCategoryByCustomerGroupIdDisableInCmsPage($customerGroupId);
        $id_path = $subject->getData('id_path');
        if ($id_path) {
            $id = str_replace('category/', '', $id_path);
        } else {
            $id = '';
        }
        if (in_array($id, $listIdSubCategory) && $enableCatalogPermission && $disableCategoryLink) {
            return false;
        }
        return $result;
    }
}
