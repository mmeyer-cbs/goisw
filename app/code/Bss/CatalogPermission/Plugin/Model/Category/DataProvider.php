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
namespace Bss\CatalogPermission\Plugin\Model\Category;

use Bss\CatalogPermission\Helper\ModuleConfig;
use Bss\CatalogPermission\Model\Category\Attribute\Source\CustomSource;
use Bss\CatalogPermission\Model\Category\Attribute\Source\RedirectType;
use Bss\CatalogPermission\Model\Config\Source\BssListCmsPage;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class DataProvider
 *
 * @package Bss\CatalogPermission\Plugin\Model\Category
 */
class DataProvider
{
    /**
     * @var array
     * @since 101.0.0
     */
    protected $meta = [];

    /**
     * @var ModuleManager
     * @since 101.0.0
     */
    protected $moduleManager;

    /**
     * @var CustomSource
     */
    protected $customSource;

    /**
     * @var ModuleConfig
     */
    protected $bssModuleConfig;

    /**
     * @var RedirectType
     */
    protected $redirectType;

    /**
     * @var BssListCmsPage
     */
    protected $bssListCmsPage;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * DataProvider constructor.
     * @param ModuleManager $moduleManager
     * @param CustomSource $customSource
     * @param ModuleConfig $bssModuleConfig
     * @param RedirectType $redirectType
     * @param BssListCmsPage $bssListCmsPage
     * @param RequestInterface $request
     */
    public function __construct(
        ModuleManager $moduleManager,
        CustomSource $customSource,
        ModuleConfig $bssModuleConfig,
        RedirectType $redirectType,
        BssListCmsPage $bssListCmsPage,
        RequestInterface $request
    ) {
        $this->moduleManager = $moduleManager;
        $this->customSource = $customSource;
        $this->bssModuleConfig = $bssModuleConfig;
        $this->redirectType = $redirectType;
        $this->bssListCmsPage = $bssListCmsPage;
        $this->request = $request;
    }

    /**
     * Add Meta to Category Form
     *
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $meta
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareMeta(
        \Magento\Catalog\Model\Category\DataProvider $subject,
        $meta
    ) {
        $data = $subject->getData();
        $meta = $this->addBssCustomerGroup($meta, $data);
        return $meta;
    }

    /**
     * Bss Customer Group MetaData
     *
     * @param array $meta
     * @param array $data
     * @return array
     */
    private function addBssCustomerGroup($meta, $data)
    {
        $params = $this->request->getParams();
        $categoryId = isset($params['id']) ? $params['id'] : null;
        $useConfig = true;
        if (isset($data[$categoryId]['bss_redirect_type']) &&
            $data[$categoryId]['bss_redirect_type'] == RedirectType::CUSTOMIZE_PER_PAGE) {
            $useConfig = false;
        }
        $meta['catalog_permission'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Catalog Permission'),
                        'collapsible' => true,
                        'sortOrder' => 6,
                        'componentType' => 'fieldset',
                    ]
                ]
            ],
            'children' => [
                'bss_customer_group' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => 'string',
                                'formElement' => 'multiselect',
                                'componentType' => 'field',
                                'options' => $this->getCustomerGroups(),
                                'label' => __('Restricted Customer Group'),
                                'scopeLabel' => __('[STORE VIEW]'),
                                'sortOrder' => 40,
                                'notice' => __('Please select customer groups to restrict access')
                            ]
                        ]
                    ]
                ],
                'bss_redirect_type' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => 'string',
                                'formElement' => 'select',
                                'componentType' => 'field',
                                'options' => $this->redirectType->toOptionArray(),
                                'label' => __('Redirect Type'),
                                'scopeLabel' => __('[STORE VIEW]'),
                                'sortOrder' => 10
                            ]
                        ]
                    ]
                ],
                'bss_select_page' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => 'string',
                                'formElement' => 'select',
                                'componentType' => 'field',
                                'options' => $this->bssListCmsPage->toOptionArray(),
                                'label' => __('Select Page'),
                                'scopeLabel' => __('[STORE VIEW]'),
                                'disabled' => $useConfig,
                                'sortOrder' => 20
                            ]
                        ]
                    ]
                ],
                'bss_custom_url' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => 'string',
                                'formElement' => 'input',
                                'componentType' => 'field',
                                'label' => __('Custom Url'),
                                'scopeLabel' => __('[STORE VIEW]'),
                                'disabled' => $useConfig,
                                'sortOrder' => 30,
                                'notice' => __('(1) Only Applied with Custom Url. (2) Example: training.html')
                            ]
                        ]
                    ]
                ],
                'bss_error_message' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => 'string',
                                'formElement' => 'input',
                                'componentType' => 'field',
                                'label' => __('Bss Error Message'),
                                'scopeLabel' => __('[STORE VIEW]'),
                                'disabled' => $useConfig,
                                'sortOrder' => 40
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $meta;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    private function getCustomerGroups()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        return $this->customSource->toOptionArray();
    }
}
