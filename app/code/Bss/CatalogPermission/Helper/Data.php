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
namespace Bss\CatalogPermission\Helper;

use Bss\CatalogPermission\Model\Category;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Cms\Helper\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Bss\CatalogPermission\Model\Config\Source\BssListCmsPage;

/**
 * Class Config
 *
 * @package Bss\CatalogPermission\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Bss\CatalogPermission\Model\Category
     */
    protected $category;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $helperCmsPage;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Config constructor.
     * @param Context $context
     * @param Category $category
     * @param Json $json
     * @param PageFactory $pageFactory
     * @param Page $helperCmsPage
     * @param SessionFactory $sessionFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        Category $category,
        Json $json,
        PageFactory $pageFactory,
        \Magento\Cms\Helper\Page $helperCmsPage,
        SessionFactory $sessionFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->category = $category;
        $this->json = $json;
        $this->pageFactory = $pageFactory;
        $this->helperCmsPage = $helperCmsPage;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function returnJson()
    {
        return $this->json;
    }

    /**
     * Helper get function
     *
     * @param int $customerGroupId
     * @param int $currentStoreId
     * @param bool $isProductPermission
     * @return array
     */
    public function getIdCategoryByCustomerGroupId(
        $customerGroupId,
        $currentStoreId,
        $isProductPermission = false
    ) {
        return $this->category
            ->getListIdCategoryByCustomerGroupId($customerGroupId, $currentStoreId, $isProductPermission);
    }

    /**
     * Helper get function
     *
     * @param int $customerGroupId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdCategoryByCustomerGroupIdDisableInCmsPage($customerGroupId)
    {
        return $this->category->getListIdCategoryByCustomerGroupIdDisableInCmsPage($customerGroupId);
    }

    /**
     * @return mixed
     */
    public function getCmsHomePage()
    {
        return $this->scopeConfig->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $identifier
     * @return string
     */
    public function buildUrl($identifier)
    {
        return $this->_urlBuilder->getUrl(null, ['_direct' => $identifier]);
    }

    /**
     * Get Redirect Url
     *
     * @param int|string $id
     * @param string $customUrl
     * @param string $referentUrl
     * @return bool|string
     */
    public function getRedirectUrl($id, $customUrl, $referentUrl)
    {
        $redirectPageUrl = $this->helperCmsPage->getPageUrl($id);
        if ($redirectPageUrl) {
            return $redirectPageUrl;
        }

        switch ($id) {
            case BssListCmsPage::SIGN_IN:
                return 'customer/account/login';
                break;
            case BssListCmsPage::CUSTOM_URL:
                return $customUrl;
                break;
            case BssListCmsPage::NONE:
                return $referentUrl;
                break;
            default:
                return false;
        }
    }

    /**
     * Get customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customer = $this->sessionFactory->create()->getCustomer();
        if (!empty($customer->getData())) {
            return $customer->getGroupId();
        }
        return 0;
    }
}
