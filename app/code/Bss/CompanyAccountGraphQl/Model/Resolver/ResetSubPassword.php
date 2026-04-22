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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Psr\Log\LoggerInterface;
use Bss\CompanyAccount\Model\SubUserManagement;

/**
 * Create provide sub-user
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ResetSubPassword extends SubUserManagement implements ResolverInterface
{
    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SubUserManagement
     */
    protected $subUserManagement;

    /**
     * @var SubUserHelper
     */
    protected $subUserHelper;

    public function __construct(
        StringHelper                   $stringHelper,
        Data                           $helper,
        SubUserHelper                  $subUserHelper,
        CustomerRepositoryInterface    $customerRepository,
        Encryptor                      $encryptor,
        DateTimeFactory                $dateTimeFactory,
        B2bRegistrationStatusValidator $b2bRegistrationStatusValidator,
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
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Resolves a value for a type or field in a schema
     *
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $subUser = $this->validate($args);
        $subEmail = $subUser['sub_email'];
        try {
            if ($subUser['sub_email']) {
                $subInfo = $this->subUserRepository->getByEmail($subEmail);
                if ($subInfo->getData()) {
                    if ($subUser['new_password'] === $subUser['password_confirm'] &&
                        $subInfo->getToken() === $subUser['token']) {
                        $this->checkPasswordStrength($subUser['new_password']);
                        $subInfo->setSubPassword($this->createPasswordHash($subUser['new_password']));
                        $subInfo->setToken(null);
                        $subInfo->setTokenExpiresAt(null);
                        $this->subUserRepository->save($subInfo);
                        return true;
                    } elseif ($subInfo->getToken() !== $subUser['token']) {
                        throw new InputException(
                            __('Token Input and Token get from Sub-User didn\'t match.')
                        );
                    } else {
                        throw new InputException(
                            __('New Password and Confirm New Password values didn\'t match.')
                        );
                    }
                } else {
                    throw new InputException(
                        __('Can\'t find Sub User.')
                    );
                }
            } else {
                throw new InputException(
                    __('Sub Email is required!.')
                );
            }
        } catch (InputException $e) {
            throw new InputException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
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
        if (!isset($args['user'])) {
            throw new GraphQlInputException(
                __("Sub-user information is required!")
            );
        } else {
            return $args['user'];
        }
    }
}
