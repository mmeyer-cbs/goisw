<?php
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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Helper;

/**
 * Class Data
 *
 * @package Bss\QuoteExtension\Helper\Email
 */
class FormatDate extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->timezone = $timezone;
        $this->productRepository = $productRepository;
    }

    /**
     * Get formatted order created date in store timezone
     *
     * @param string $time
     * @param int $store
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getCreatedAtFormatted($time, $store, $format)
    {
        return $this->timezone->formatDateTime(
            $this->getNewDate($time),
            $format,
            $format,
            null,
            $this->timezone->getConfigTimezone('store', $store)
        );
    }

    /**
     * Format date time
     *
     * @param string $date
     * @param int $format
     * @param bool $showTime
     * @param string $timezone
     * @return string
     * @throws \Exception
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : $this->getNewDate($date);
        return $this->timezone->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * Get Current Date
     *
     * @return string
     */
    public function getCurrentDate()
    {
        return $this->timezone->date()->format('Y-m-d H:i:s');
    }

    /**
     * Format new date
     *
     * @param string $date
     * @return \DateTime
     */
    public function getNewDate($date)
    {
        return $this->timezone->date($date);
    }

    /**
     * Get product from quote item
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($item)
    {
        $product = $item->getProduct();
        if ($item->getProduct()->getTypeId() == "configurable") {
            $product = $this->productRepository->get($item->getSku());
        }
        return $product;
    }
}
