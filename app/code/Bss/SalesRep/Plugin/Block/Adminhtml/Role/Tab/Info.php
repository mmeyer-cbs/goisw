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
namespace Bss\SalesRep\Plugin\Block\Adminhtml\Role\Tab;

use Bss\SalesRep\Helper\Data;
use Magento\Authorization\Model\Role;
use Magento\Framework\App\RequestInterface;

/**
 * Class Info
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Role\Tab
 */
class Info
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Role
     */
    protected $role;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Info constructor.
     * @param Data $helper
     * @param RequestInterface $request
     * @param Role $role
     */
    public function __construct(
        \Bss\SalesRep\Helper\Data $helper,
        RequestInterface $request,
        Role $role
    ) {
        $this->request = $request;
        $this->role = $role;
        $this->helper = $helper;
    }

    /**
     * Get form HTML
     *
     * @param \Magento\User\Block\Role\Tab\Info $subject
     * @return array
     */
    public function beforeGetFormHtml(
        \Magento\User\Block\Role\Tab\Info $subject
    ) {
        $id = $this->request->getParam('rid');
        $role = $this->role->load($id)->getData();
        $isSalesRep = 0;
        if (!empty($role)) {
            $isSalesRep = $role['is_sales_rep'];
        }
        $form = $subject->getForm();
        if ($this->helper->isEnable()) {
            if (is_object($form)) {
                $baseFieldset = $form->getElement('base_fieldset');
                $baseFieldset->addField(
                    'is_sales_rep',
                    'select',
                    [
                        'name' => 'is_sales_rep',
                        'label' => __('Sales Rep Role'),
                        'title' => __('Sales Rep Role'),
                        'id' => 'is_sales_rep',
                        'value' => $isSalesRep,
                        'options' => ['0' => __('Admin Sales Rep'), '1' => __('Sales Rep')],
                        'class' => 'select',
                    ]
                );
                $subject->setForm($form);
            }
        }
        return [];
    }
}
