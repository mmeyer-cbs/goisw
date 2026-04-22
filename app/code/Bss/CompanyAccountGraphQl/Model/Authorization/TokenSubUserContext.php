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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccountGraphQl\Model\Authorization;

use Bss\CompanyAccountGraphQl\Model\UserContextInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Framework\Webapi\Request;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Webapi\Model\Authorization\TokenUserContext;

/**
 * Class TokenUserContext
 * Custom for sub-user token
 */
class TokenSubUserContext extends TokenUserContext implements UserContextInterface
{
    /**
     * Auth sub-user id
     *
     * @var int
     */
    protected $subUserId;

    /**
     * @var OauthHelper|null
     */
    protected $oauthHelper;

    /**
     * @var DateTime|null
     */
    protected $dateTime;

    /**
     * @var Date|null
     */
    protected $date;

    /**
     * TokenUserContext constructor.
     *
     * @param Request $request
     * @param TokenFactory $tokenFactory
     * @param IntegrationServiceInterface $integrationService
     * @param DateTime|null $dateTime
     * @param Date|null $date
     * @param OauthHelper|null $oauthHelper
     */
    public function __construct(
        Request $request,
        TokenFactory $tokenFactory,
        IntegrationServiceInterface $integrationService,
        DateTime $dateTime = null,
        Date $date = null,
        OauthHelper $oauthHelper = null
    ) {
        parent::__construct($request, $tokenFactory, $integrationService, $dateTime, $date, $oauthHelper);
        $this->oauthHelper = $oauthHelper;
        $this->dateTime = $dateTime;
        $this->date = $date;
    }

    /**
     * Get auth sub-user id
     *
     * @return int|null
     */
    public function getSubUserId(): ?int
    {
        $this->processRequest();
        return $this->subUserId;
    }

    /**
     * Set user data based on user type received from token data.
     *
     * Custom for sub-user type
     *
     * @param Token $token
     */
    protected function setUserDataViaToken(Token $token)
    {
        $this->userType = $token->getUserType();

        // Set sub-user id for auth context
        if ((int) $this->userType === self::USER_TYPE_SUB_USER) {
            if ($this->isSubTokenExpired($token)) {
                $this->isRequestProcessed = true;
                return;
            }

            $this->subUserId = (int) $token->getSubUserId();
            $this->userId = $token->getCustomerId();
            $this->userType = self::USER_TYPE_CUSTOMER;
            return;
        }

        parent::setUserDataViaToken($token);
    }

    /**
     * Check if token is expired.
     *
     * @param Token $token
     * @return bool
     */
    private function isSubTokenExpired(Token $token): bool
    {
        $tokenTtl = $this->oauthHelper->getCustomerTokenLifetime();

        if (empty($tokenTtl)) {
            return false;
        }

        if ($this->dateTime->strToTime($token->getCreatedAt()) < ($this->date->gmtTimestamp() - $tokenTtl * 3600)) {
            return true;
        }

        return false;
    }
}
