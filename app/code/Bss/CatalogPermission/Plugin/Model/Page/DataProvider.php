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
namespace Bss\CatalogPermission\Plugin\Model\Page;

use Magento\Framework\Json\Helper\Data as Json;

/**
 * Class DataProvider
 *
 * @package Bss\CatalogPermission\Plugin\Model\Page
 */
class DataProvider
{
    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * DataProvider constructor.
     * @param \Bss\CatalogPermission\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Bss\CatalogPermission\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Plugin after get data
     *
     * @param \Magento\Cms\Model\Page\DataProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        \Magento\Cms\Model\Page\DataProvider $subject,
        $result
    ) {
        if (is_array($result)) {
            foreach ($result as &$item) {
                if (isset($item['bss_customer_group']) && ($item['bss_customer_group'])) {
                    $item['bss_customer_group'] = $this->helper
                        ->returnJson()
                        ->unserialize($item['bss_customer_group']);
                }
            }
        }
        return $result;
    }

    /**
     * Plugin after get meta
     *
     * Disabled if bss_select_page and bss_custom_url if bss_redirect_type = 'use-config'
     *
     * @param \Magento\Cms\Model\Page\DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetMeta(
        \Magento\Cms\Model\Page\DataProvider $subject,
        $result
    ) {
        $params = $this->request->getParams();
        $pageId = isset($params['page_id']) ? $params['page_id'] : null;
        $useConfig = true;
        $data = $subject->getData();
        if (isset($data[$pageId]['bss_redirect_type']) && $data[$pageId]['bss_redirect_type'] == 2) {
            $useConfig = false;
        }
        
        $result['catalog_permission_page'] = [
            'children' => [
                'bss_select_page' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'disabled' => $useConfig,
                            ],
                        ],
                    ],
                ],
                'bss_custom_url' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'disabled' => $useConfig,
                            ],
                        ],
                    ],
                ],
                'bss_error_message' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'disabled' => $useConfig,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return $result;
    }
}
