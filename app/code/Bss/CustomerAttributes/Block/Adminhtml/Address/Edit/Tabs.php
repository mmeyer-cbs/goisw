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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Block\Adminhtml\Address\Edit;

use Bss\CustomerAttributes\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class Tabs
 *
 * @package Bss\CustomerAttributes\Block\Adminhtml\Address\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context          $context,
        EncoderInterface $jsonEncoder,
        Session          $authSession,
        Data             $helperData,
        array            $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->helperData = $helperData;
    }

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('address_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Address Information'));
    }

    /**
     * Before to Html
     *
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Exception
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main',
            [
                'label' => __('Properties'),
                'title' => __('Properties'),
                'content' => $this->getChildHtml('main'),
                'active' => true
            ]
        );

        $this->addTab(
            'labels',
            [
                'label' => __('Manage Labels'),
                'title' => __('Manage Labels'),
                'content' => $this->getChildHtml('labels')
            ]
        );
        $this->addTab(
            'front',
            [
                'label' => __('Display Configuration'),
                'title' => __('Display Configuration'),
                'content' => $this->getChildHtml('front')
            ]
        );
        if ($this->helperData->isEnableCustomerAttributeDependency()) {
            $this->addTab(
                'relation',
                [
                    'label' => __('Manage Relation'),
                    'title' => __('Manage Relation'),
                    'content' => $this->getChildHtml('relation')
                ]
            );
        }

        return parent::_beforeToHtml();
    }
}
