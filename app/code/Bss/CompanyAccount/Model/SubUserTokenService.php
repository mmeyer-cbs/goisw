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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Api\SubUserManagementInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Bss\CompanyAccount\Model\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Providing token generation for company account sub-user
 */
class SubUserTokenService
{
    const USER_TYPE_SUB_USER = 5;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    protected $validator;

    /**
     * @var RequestThrottler
     */
    protected $requestThrottler;

    /**
     * @var SubUserManagementInterface
     */
    protected $subUserManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var TokenModelFactory
     */
    protected $tokenModelFactory;

    /**
     * @var TokenCollectionFactory
     */
    protected $tokenModelCollectionFactory;

    /**
     * SubUserTokenService constructor.
     *
     * @param \Magento\Integration\Model\CredentialsValidator $validator
     * @param RequestThrottler $requestThrottler
     * @param SubUserManagementInterface $subUserManagement
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $eventManager
     * @param TokenModelFactory $tokenModelFactory
     * @param TokenCollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Integration\Model\CredentialsValidator $validator,
        RequestThrottler $requestThrottler,
        SubUserManagementInterface $subUserManagement,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        TokenModelFactory $tokenModelFactory,
        TokenCollectionFactory $collectionFactory
    ) {
        $this->validator = $validator;
        $this->requestThrottler = $requestThrottler;
        $this->subUserManagement = $subUserManagement;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->tokenModelCollectionFactory = $collectionFactory;
    }

    /**
     * Create Sub-user access token
     *
     * @throws InputException|AuthenticationException
     */
    public function createSubUserAccessToken($username, $password)
    {
        $this->validator->validate($username, $password);
        $this->requestThrottler->throttle($username, self::USER_TYPE_SUB_USER);
        try {
            $subUser = $this->subUserManagement->getSubUserBy(
                $username,
                "sub_email",
                $this->storeManager->getWebsite()->getId()
            );
            if (!$this->subUserManagement->authenticate($subUser, $password)) {
                throw new AuthenticationException(__("Authentication failed"));
            }

            $this->eventManager->dispatch('customer_login', ['customer' => $subUser->customer()]);
            $this->eventManager->dispatch('sub_user_login', ['sub_user' => $subUser]);
            $this->requestThrottler->resetAuthenticationFailuresCount($username, self::USER_TYPE_SUB_USER);
            return $this->tokenModelFactory->create()->createSubUserToken($subUser->getSubId())->getToken();
        } catch (\Exception $e) {
            $this->requestThrottler->logAuthenticationFailure($username, self::USER_TYPE_SUB_USER);
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
    }

    /**
     * Revoke token by sub-user id.
     *
     * The function will delete the token from the oauth_token table.
     *
     * @param int $subId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeSubUserAccessToken($subId): bool
    {
        $tokenCollection = $this->tokenModelCollectionFactory->create()
            ->addFilter('main_table.sub_user_id', $subId);
        if ($tokenCollection->getSize() == 0) {
            throw new LocalizedException(__('This sub-user has no tokens.'));
        }
        try {
            foreach ($tokenCollection as $token) {
                $token->delete();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__("The tokens couldn't be revoked."));
        }
        return true;
    }
}
