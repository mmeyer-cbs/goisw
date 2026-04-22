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
namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Api\SubUserManagementInterface;
use Bss\CompanyAccount\Exception\B2bRegistrationStatusException;
use Bss\CompanyAccount\Exception\EmptyInputException;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\StringUtils as StringHelper;

/**
 * Class SubUserManagement
 *
 * @package Bss\CompanyAccount\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubUserManagement implements SubUserManagementInterface
{
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var StringHelper
     */
    private $stringHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var SubUserHelper
     */
    private $subUserHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var B2bRegistrationStatusValidator
     */
    protected $b2bRegistrationStatusValidator;

    /**
     * SubUserManagement constructor.
     *
     * @param StringHelper $stringHelper
     * @param Data $helper
     * @param SubUserHelper $subUserHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param Encryptor $encryptor
     * @param DateTimeFactory $dateTimeFactory
     * @param B2bRegistrationStatusValidator $b2bRegistrationStatusValidator
     */
    public function __construct(
        StringHelper $stringHelper,
        Data $helper,
        SubUserHelper $subUserHelper,
        CustomerRepositoryInterface $customerRepository,
        Encryptor $encryptor,
        DateTimeFactory $dateTimeFactory,
        B2bRegistrationStatusValidator $b2bRegistrationStatusValidator
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->stringHelper = $stringHelper;
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->subUserHelper = $subUserHelper;
        $this->customerRepository = $customerRepository;
        $this->b2bRegistrationStatusValidator = $b2bRegistrationStatusValidator;
    }

    /**
     * Check if reset password link token is valid
     *
     * @param int $subId
     * @param string $token
     * @param null|int $websiteId
     * @return bool
     * @throws ExpiredException
     * @throws InputException
     * @throws LocalizedException
     */
    public function validateResetPasswordLinkToken($subId, $token, $websiteId = null)
    {
        try {
            $this->validateResetPasswordToken($subId, $token, $websiteId);
        } catch (InputException $e) {
            throw new InputException(__($e->getMessage()));
        } catch (ExpiredException $e) {
            throw new ExpiredException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return true;
    }

    /**
     * Validate reset password token for sub-user
     *
     * @param int $subId
     * @param string $token
     * @param null|int $websiteId
     *
     * @return bool
     * @throws ExpiredException
     * @throws InputException
     * @throws LocalizedException
     */
    public function validateResetPasswordToken($subId, $token, $websiteId = null)
    {
        if (!is_string($token) || empty($token)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__('"%fieldName" is required. Enter and try again.', $params));
        }
        try {
            if ($subId === null) {
                $subUser = $this->getSubUserBy($token, 'token', $websiteId);
            } else {
                $subUser = $this->getSubUserBy($subId, 'sub_id', $websiteId);
            }
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        if ($subUser) {
            $expiresAt = $subUser->getTokenExpiresAt();
            if ($this->isTokenExpired($expiresAt)) {
                throw new ExpiredException(__('The password token is expired. Reset and try again.'));
            }
            return true;
        }
        throw new ExpiredException(__('The password token is incorrect. Reset and try again.'));
    }

    /**
     * Get sub-user by key
     *
     * @param string|int $value
     * @param string $key
     * @param null|int $websiteId
     *
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserInterface
     * @throws LocalizedException
     */
    public function getSubUserBy($value, $key = 'sub_id', $websiteId = null)
    {
        try {
            $subUsers = $this->subUserHelper->getListBy($value, $key);
            if ($subUsers) {
                foreach ($subUsers as $subUser) {
                    $customer = $this->customerRepository->getById($subUser->getCustomerId());
                    if ((int) $customer->getWebsiteId() === (int) $websiteId) {
                        return $subUser;
                    }
                }
            }
            return false;
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Get related customer
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @param int $websiteId
     * @return \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer
     * @throws LocalizedException
     */
    public function getCustomerBySubUser($subUser, $websiteId = null)
    {
        try {
            $customer = $this->customerRepository->getById($subUser->getCustomerId());
            if ($websiteId === null) {
                return $customer;
            }
            return $this->customerRepository->get($customer->getEmail(), $websiteId);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Your account not allow in this website.')
            );
        }
    }

    /**
     * The token is expired
     *
     * @param string $expiresAt
     * @return bool
     */
    private function isTokenExpired($expiresAt)
    {
        if (empty($expiresAt)) {
            return true;
        }
        $tokenTimestamp = $this->dateTimeFactory->create($expiresAt)->getTimestamp();
        $currentTimestamp = $this->dateTimeFactory->create()->getTimestamp();

        if ($tokenTimestamp >= $currentTimestamp) {
            return false;
        }
        return true;
    }

    /**
     * Reset sub-user password
     *
     * @param int|null $subId
     * @param string $token
     * @param string $password
     * @param int $websiteId
     *
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     */
    public function resetPassword($subId, $token, $password, $websiteId)
    {
        try {
            if ($subId === null) {
                $subUser = $this->getSubUserBy($token, 'token', $websiteId);
            } else {
                $subUser = $this->getSubUserBy($subId, 'sub_id', $websiteId);
            }

            $this->checkPasswordStrength($password);
            $subUser->setSubPassword($this->createPasswordHash($password));
            $subUser->setToken(null);
            $subUser->setTokenExpiresAt(null);

            $this->subUserHelper->save($subUser);

            return true;
        } catch (InputException $e) {
            throw new InputException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Authenticate sub-user password
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @param string $password
     *
     * @return bool
     * @throws EmptyInputException|B2bRegistrationStatusException|AuthenticationException
     */
    public function authenticate($subUser, $password)
    {
        if (empty($password)) {
            throw new EmptyInputException(__('Please enter your current password.'));
        }

        $this->b2bRegistrationStatusValidator->validate($subUser->customer());

        try {
            return $this->encryptor->validateHash($password, $subUser->getSubPassword());
        } catch (\Exception $e) {
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
    }

    /**
     * Edit a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * Make sure that password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length > self::MAX_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at most %1 characters.',
                    self::MAX_PASSWORD_LENGTH
                )
            );
        }
        $configMinPasswordLength = $this->helper->getMinPasswordLength();
        if ($length < $configMinPasswordLength) {
            throw new InputException(
                __(
                    'The password needs at least %1 characters. Edit a new password and try again.',
                    $configMinPasswordLength
                )
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException(
                __("The password can't begin or end with a space. Verify the password and try again.")
            );
        }

        $requiredCharactersCheck = $this->helper->makeRequiredCharactersCheck($password);
        if ($requiredCharactersCheck !== 0) {
            throw new InputException(
                __(
                    'Minimum of different classes of characters in password is %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $requiredCharactersCheck
                )
            );
        }
    }
}
