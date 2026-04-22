<?php
declare(strict_types = 1);

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
namespace Bss\CompanyAccount\Helper;

use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class FormHelper
 *
 * @package Bss\CompanyAccount\Helper
 */
class FormHelper
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * FormHelper constructor.
     *
     * @param ManagerInterface $messageManager
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        ManagerInterface $messageManager,
        FormKeyValidator $formKeyValidator
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
    }

    /**
     * Validate form key
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function validate($request)
    {
        if ($this->formKeyValidator->validate($request)) {
            return true;
        }
        $this->messageManager->addErrorMessage(__('Invalid Form Key. Please refresh the page'));
        return false;
    }
}
