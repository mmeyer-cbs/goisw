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
namespace  Bss\CompanyAccount\Plugin\QuoteExtension\Block;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Class MyQuote
 *
 * @package Bss\CompanyAccount\Plugin\Block\View
 */
class History
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * MyQuote constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param Registry $registry
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Registry $registry,
        PermissionsChecker $permissionsChecker
    ) {
        $this->logger = $logger;
        $this->registry = $registry;
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Check history quote with sub-user Company Account
     *
     * @param Object $subject
     * @param Object $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterGetRequestQuotes($subject, $result)
    {
        $subUserId = $this->permissionsChecker->getSubId();
        $allowViewAllQuotes = $this->permissionsChecker->allowQuote(Permissions::VIEW_QUOTES);
        if($result && $subUserId && !$allowViewAllQuotes) {
            try{
                $result->addFieldToFilter("bss_sub_user.sub_id", $subUserId);
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
        return $result;
    }
}
