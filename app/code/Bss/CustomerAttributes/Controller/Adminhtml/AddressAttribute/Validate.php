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

namespace Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute;


use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Url;
use Magento\Customer\Model\Attribute;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutFactory;

/**
 * Class Validate
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute
 */
class Validate extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Validate
{
    /**
     * Validate constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param Attribute $attribute
     * @param Url $productUrl
     * @param DataObject $response
     */
    public function __construct(
        Context       $context,
        JsonFactory   $resultJsonFactory,
        LayoutFactory $layoutFactory,
        Attribute     $attribute,
        Url           $productUrl,
        DataObject    $response
    ) {
        parent::__construct($context, $resultJsonFactory, $layoutFactory, $attribute, $productUrl, $response);
    }

    /**
     * Validate execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return parent::execute();
    }
}
