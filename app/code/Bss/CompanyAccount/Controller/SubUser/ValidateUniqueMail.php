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
namespace Bss\CompanyAccount\Controller\SubUser;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Class ValidateUniqueMail
 *
 * @package Bss\CompanyAccount\Controller\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class ValidateUniqueMail extends \Magento\Framework\App\Action\Action
{
    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * ValidateUniqueMail constructor.
     *
     * @param SubUserRepositoryInterface $subUserRepository
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        SubUserRepositoryInterface $subUserRepository,
        JsonFactory $jsonFactory,
        Context $context
    ) {
        $this->subUserRepository = $subUserRepository;
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $email = $this->getRequest()->getParam('sub_email');
        $subId = $this->getRequest()->getParam('sub_id');
        $resultJson = $this->jsonFactory->create();
        try {
            $this->subUserRepository->validateUniqueSubMail($customerId, $email, $subId);
            $resultJson->setData('true');
        } catch (AlreadyExistsException $e) {
            $resultJson->setData($e->getMessage());
        }
        return $resultJson;
    }
}
