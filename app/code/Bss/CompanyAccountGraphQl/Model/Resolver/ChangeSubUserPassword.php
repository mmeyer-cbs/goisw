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
 * @package    Bss_CompanyAccountGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccountGraphQl\Model\Resolver;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Bss\CompanyAccount\Model\B2bRegistrationStatusValidator;
use Bss\CompanyAccount\Model\SubUserManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\StringUtils as StringHelper;

class ChangeSubUserPassword extends SubUserManagement implements ResolverInterface
{
    /**
     * @var SubUserManagement
     */
    protected $subUserManagement;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    public function __construct(
        StringHelper                   $stringHelper,
        Data                           $helper,
        SubUserHelper                  $subUserHelper,
        CustomerRepositoryInterface    $customerRepository,
        Encryptor                      $encryptor,
        DateTimeFactory                $dateTimeFactory,
        B2bRegistrationStatusValidator $b2bRegistrationStatusValidator,
        SubUserManagement              $subUserManagement,
        SubUserRepositoryInterface     $subUserRepository
    ) {
        parent::__construct(
            $stringHelper,
            $helper,
            $subUserHelper,
            $customerRepository,
            $encryptor,
            $dateTimeFactory,
            $b2bRegistrationStatusValidator
        );
        $this->subUserManagement = $subUserManagement;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Resolves function change password for SubUser
     *
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $subUser = $this->validate($args);
        if ($subUser['sub_email']) {
            $subEmail = $subUser['sub_email'];
            $subInfo = $this->subUserRepository->getByEmail($subEmail);
            try {
                if ($this->authenticate($subInfo, $subUser['sub_password'])) {
                    if ($subUser['new_password'] === $subUser['password_confirm']) {
                        $this->checkPasswordStrength($subUser['new_password']);
                        $subInfo->setSubPassword($this->createPasswordHash($subUser['new_password']));
                        $this->subUserRepository->save($subInfo);
                    } else {
                        throw new InputException(
                            __('New Password and Confirm New Password values didn\'t match.')
                        );
                    }
                } else {
                    throw new InputException(
                        __('Incorrect Password. Please try again!')
                    );
                }
                return true;
            } catch (InputException $e) {
                throw new InputException(__($e->getMessage()));
            } catch (LocalizedException $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
        }
        return false;
    }

    /**
     * Validate params
     *
     * @param array|null $args
     * @return array|null
     * @throws GraphQlInputException
     */
    public function validate(array $args = null)
    {
        if (!isset($args['user']) ||
            !isset($args['user']['sub_email']) ||
            !isset($args['user']['sub_password']) ||
            !isset($args['user']['new_password']) ||
            !isset($args['user']['password_confirm'])) {
            throw new GraphQlInputException(
                __("Please enter enough information!")
            );
        } else {
            return $args['user'];
        }
    }
}
