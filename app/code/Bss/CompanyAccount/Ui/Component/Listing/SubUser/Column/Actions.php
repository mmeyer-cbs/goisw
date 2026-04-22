<?php
declare(strict_types=1);

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
namespace Bss\CompanyAccount\Ui\Component\Listing\SubUser\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Prepare actions column for sub-user grid
 */
class Actions extends Column
{
    const CUSTOMER_SUB_USER_PATH_DELETE = 'bss_companyaccount/customer_subuser/delete';
    const CUSTOMER_SUB_USER_PATH_RESET_PASS = 'bss_companyaccount/customer_subuser/resetpassword';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource($dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['sub_id'])) {
                    if ($item['customer_id'] !== null) {
                        $item[$name]['edit'] = [
                            'callback' => [
                                [
                                    'provider' => 'customer_form.areas.bss_company_account_manage_sub_user.'
                                        . 'bss_company_account_manage_sub_user.'
                                        . 'bss_companyaccount_customer_subuser_update_modal.'
                                        . 'update_bss_companyaccount_customer_subuser_form_loader',
                                    'target' => 'destroyInserted',
                                ],
                                [
                                    'provider' => 'customer_form.areas.bss_company_account_manage_sub_user.'
                                        . 'bss_company_account_manage_sub_user.'
                                        . 'bss_companyaccount_customer_subuser_update_modal',
                                    'target' => 'openModal',
                                ],
                                [
                                    'provider' => 'customer_form.areas.bss_company_account_manage_sub_user.'
                                        . 'bss_company_account_manage_sub_user.'
                                        . 'bss_companyaccount_customer_subuser_update_modal.'
                                        . 'update_bss_companyaccount_customer_subuser_form_loader',
                                    'target' => 'render',
                                    'params' => [
                                        'sub_id' => $item['sub_id'],
                                    ],
                                ]
                            ],
                            'href' => '#',
                            'label' => __('Edit'),
                            'hidden' => false,
                        ];

                        $item[$name]['delete'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::CUSTOMER_SUB_USER_PATH_DELETE,
                                ['customer_id' => $item['customer_id'], 'id' => $item['sub_id']]
                            ),
                            'label' => __('Delete'),
                            'isAjax' => true,
                            'confirm' => [
                                'title' => __('Delete sub-user'),
                                'message' => __('Are you sure you want to delete the sub-user?')
                            ]
                        ];
                        $item[$name]['reset_password'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::CUSTOMER_SUB_USER_PATH_RESET_PASS,
                                ['customer_id' => $item['customer_id'], 'id' => $item['sub_id']]
                            ),
                            'label' => __('Reset password'),
                            'isAjax' => true,
                            'confirm' => [
                                'title' => __('Reset password sub-user'),
                                'message' => __('Are you sure you want to send reset password to the sub-user?')
                            ]
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
