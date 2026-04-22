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

use Magento\Backend\App\Action;
use Magento\Customer\Model\Attribute;

/**
 * Class Delete
 * @SuppressWarnings(PHPMD)
 */
class Delete extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Delete
{
    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param Attribute $model
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Attribute $model
    ) {
        parent::__construct($context, $model);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return parent::execute();
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::customer_attributes_delete');
    }
}
