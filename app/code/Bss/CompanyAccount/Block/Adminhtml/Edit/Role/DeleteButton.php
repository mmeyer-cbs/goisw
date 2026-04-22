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
namespace Bss\CompanyAccount\Block\Adminhtml\Edit\Role;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Bss\CompanyAccount\Ui\Component\Listing\Role\Column\Actions;

/**
 * Delete button on edit customer address form
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get delete button data.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getRoleId()) {
            $data = [
                'label' => __('Delete'),
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'bss_companyaccount_customer_listroles_form.'
                                        . 'bss_companyaccount_customer_listroles_form',
                                    'actionName' => 'deleteRole',
                                    'params' => [
                                        $this->getDeleteUrl(),
                                    ],

                                ]
                            ],
                        ],
                    ],
                ],
                'sort_order' => 20
            ];
        }
        return $data;
    }

    /**
     * Get delete button url.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDeleteUrl(): string
    {
        return $this->getUrl(
            Actions::CUSTOMER_ROLES_PATH_DELETE,
            ['customer_id' => $this->getCustomerId(), 'id' => $this->getRoleId()]
        );
    }
}
