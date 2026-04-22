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

namespace Bss\CompanyAccount\Helper;

use Magento\Customer\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class HelperData
 *
 * @package Bss\CompanyAccount\Helper
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HelperData
{
    /**
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var SessionManager
     */
    private $coreSession;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var ProductMetadataInterface
     */
    private $magentoVersion;

    /**
     * HelperData constructor.
     *
     * @param ProductMetadataInterface $magentoVersion
     * @param SenderResolverInterface $senderResolver
     * @param SessionManager $coreSession
     * @param Data $priceHelper
     * @param ObjectManagerInterface $objectManager
     * @param DateTimeFactory $dateTimeFactory
     * @param Currency $currency
     * @param Session $customerSession
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProductMetadataInterface                                 $magentoVersion,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        SessionManager                                           $coreSession,
        \Magento\Framework\Pricing\Helper\Data                   $priceHelper,
        \Magento\Framework\ObjectManagerInterface                $objectManager,
        DateTimeFactory                                          $dateTimeFactory,
        \Magento\Directory\Model\Currency                        $currency,
        Session                                                  $customerSession,
        \Magento\Framework\Api\SearchCriteriaBuilder             $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder                     $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder         $filterGroupBuilder
    ) {
        $this->magentoVersion = $magentoVersion;
        $this->senderResolver = $senderResolver;
        $this->coreSession = $coreSession;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->customerSession = $customerSession;
        $this->currency = $currency;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->objectManager = $objectManager;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Get price helper
     *
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getPriceHelper()
    {
        return $this->priceHelper;
    }

    /**
     * Get object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Get currency object
     *
     * @return \Magento\Directory\Model\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get dateTime object
     *
     * @return DateTimeFactory
     */
    public function getDateTimeFactory()
    {
        return $this->dateTimeFactory;
    }

    /**
     * Get customer session object
     *
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Get sender Resolver
     *
     * @return \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    public function getSenderResolver()
    {
        return $this->senderResolver;
    }

    /**
     * Get Search Criteria Builder
     *
     * @return \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder()
    {
        return $this->criteriaBuilder;
    }

    /**
     * Get filter builder
     *
     * @return \Magento\Framework\Api\FilterBuilder
     */
    public function getFilterBuilder()
    {
        return $this->filterBuilder;
    }

    /**
     * Get filter group builder
     *
     * @return \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    public function getFilterGroupBuilder()
    {
        return $this->filterGroupBuilder;
    }

    /**
     * Get core session
     *
     * @return SessionManager
     */
    public function getCoreSession()
    {
        return $this->coreSession;
    }

    /**
     * Check magento >= 2.4.4 or not
     *
     * @return bool|int
     */
    public function checkMagentoVersionHigherV244()
    {
        $magentoVer = $this->magentoVersion->getVersion();
        return version_compare($magentoVer, '2.4.4', '>=');
    }
}
