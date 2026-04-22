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

use Bss\CompanyAccount\Api\Data\SubUserInterface as SubUser;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Exception\EmailValidateException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class SubUserHelper
 *
 * @package Bss\CompanyAccount\Helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubUserHelper
{
    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory
     */
    private $subUserFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * GetSubUserByToken constructor.
     *
     * @param DateTimeFactory $dateTimeFactory
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param SubUserRepositoryInterface $subUserRepository
     * @param EmailHelper $emailHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param Data $helper
     */
    public function __construct(
        DateTimeFactory                                      $dateTimeFactory,
        \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserFactory,
        \Magento\Framework\Math\Random                       $mathRandom,
        SubUserRepositoryInterface                           $subUserRepository,
        EmailHelper                                          $emailHelper,
        CustomerRepositoryInterface                          $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder         $criteriaBuilder,
        \Bss\CompanyAccount\Helper\Data                      $helper
    ) {
        $this->subUserRepository = $subUserRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->mathRandom = $mathRandom;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->subUserFactory = $subUserFactory;
        $this->customerRepository = $customerRepository;
        $this->emailHelper = $emailHelper;
        $this->helper = $helper;
    }

    /**
     * Get a sub-user by key
     *
     * @param $value
     * @param $key
     * @return SubUser|false
     * @throws \Bss\CompanyAccount\Exception\RelationMethodNotFoundException
     */
    public function getBy($value, $key = 'sub_id')
    {
        $this->criteriaBuilder->addFilter(
            $key,
            $value
        );
        $this->criteriaBuilder->setPageSize(1);
        $found = $this->subUserRepository->getList(
            $this->criteriaBuilder->create()
        );

        foreach ($found->getItems() as $item) {
            return $item;
        }
        return false;
    }

    /**
     * Get list sub-user by key
     *
     * @param $value
     * @param $key
     * @return SubUser[]|false
     * @throws \Bss\CompanyAccount\Exception\RelationMethodNotFoundException
     */
    public function getListBy($value, $key = 'sub_id')
    {
        $this->criteriaBuilder->addFilter(
            $key,
            $value
        );
        $found = $this->subUserRepository->getList(
            $this->criteriaBuilder->create()
        );

        if ($found->getTotalCount() > 0) {
            return $found->getItems();
        }
        return false;
    }

    /**
     * Save sub-user helper
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     *
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function save($subUser)
    {
        try {
            return $this->subUserRepository->save($subUser);
        } catch (AlreadyExistsException $e) {
            throw new AlreadyExistsException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * Generate reset password token for sub-user
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface
     *
     * @throws LocalizedException
     */
    public function generateResetPasswordToken($subUser)
    {
        $token = $this->mathRandom->getRandomString(10);
        $expiresAt = $this->dateTimeFactory->create();
        $expiresAt->add(new \DateInterval('P3D'));

        $subUser->setTokenExpiresAt($expiresAt->format('Y-m-d H:i:s'));
        $subUser->setToken($token);

        return $subUser;
    }

    /**
     * Generate reset password token for sub-user
     *
     * @return string
     * @throws LocalizedException
     */
    public function generateResetPasswordTokenHash()
    {
        $token = $this->mathRandom->getRandomString(10);
        return $token;
    }

    /**
     * Generate reset password expires token for sub-user
     *
     * @return string
     */
    public function generateResetPasswordTokenExpires()
    {
        $expiresAt = $this->dateTimeFactory->create();
        $expiresAt->add(new \DateInterval('P3D'));
        return $expiresAt->format('Y-m-d H:i:s');
    }

    /**
     * Retrieve new sub-user
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param int $customerId
     *
     * @param string $messageErrorEmail
     * @param null $createdSubUser
     * @return string|\Bss\CompanyAccount\Model\SubUser
     * @throws AlreadyExistsException
     * @throws EmailValidateException
     * @throws LocalizedException
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createSubUser($request, $customerId, &$messageErrorEmail = null, &$createdSubUser = null)
    {
        $subId = $request->getParam('sub_id', '');
        $isNew = false;
        $isSendMail = true;
        /** @var \Bss\CompanyAccount\Model\SubUser $user */
        $user = $this->subUserFactory->create();
        $user->setSubName($request->getParam(SubUser::NAME));
        $user->setRoleId((int)$request->getParam(SubUser::ROLE_ID));
        $user->setSubStatus((int)$request->getParam(SubUser::STATUS));
        $user->setCustomerId($customerId);
        if (!empty($subId) || (int)$subId !== 0) {
            try {
                $currentSubUser = $this->subUserRepository->getById($subId);
            } catch (\Exception $e) {
                throw new NotFoundException(__("Can not find this sub-user."));
            }
            if ($currentSubUser) {
                $isSendMail = $currentSubUser->getIsSentMail();
                $user->setSubEmail($currentSubUser->getSubEmail());
            }
            $user->setSubId($subId);
            $message = __('Sub-user has been updated.');
        } else {
            $user->setSubId(null);
            $email = $request->getParam(SubUser::EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new EmailValidateException(__("Please input non-unicode character for email."));
            }
            $user->setSubEmail($email);
            $message = __('New sub-user has been added.');
            $isNew = true;
        }
        if ($isNew || !$isSendMail) {
            if ($user->getSubStatus()) {
                $user->setIsSentMail(1);
                $user = $this->generateResetPasswordToken($user);
            }
        }
        $this->save($user);
        $createdSubUser = $user;

        if ($isNew || !$isSendMail) {
            if ($user->getSubStatus()) {
                /** @var \Magento\Customer\Model\Customer $customer */
                $customer = $this->customerRepository->getById($customerId);
                try {
                    $this->emailHelper->sendWelcomeMailToSubUser($customer, $user);
                } catch (\Exception $exception) {
                    $messageErrorEmail = __('We can\'t send email to new sub-user!');
                }
            }
        }
        return $message;
    }
}
