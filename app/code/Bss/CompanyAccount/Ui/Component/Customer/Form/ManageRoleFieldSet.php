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
namespace Bss\CompanyAccount\Ui\Component\Customer\Form;

use Bss\CompanyAccount\Helper\Data;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ManageRoleFieldSet
 *
 * @package Bss\CompanyAccount\Ui\Component\customer\Form
 */
class ManageRoleFieldSet extends \Magento\Ui\Component\Form\Fieldset implements ComponentVisibilityInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param ContextInterface $context
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        ContextInterface $context,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->context = $context;
        $this->helper = $helper;
        parent::__construct($context, $components, $data);
    }

    /**
     * Can show manage role tab in tabs or not
     *
     * Will return false for not is company account
     *
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isComponentVisible(): bool
    {
        $customerId = $this->context->getRequestParam('id');
        if ($customerId) {
            $customer = $this->customerRepository->getById((int) $customerId);
            return $this->helper->isCompanyAccount($customer) && $this->helper->isEnable($customer->getWebsiteId());
        }
        return false;
    }
}
