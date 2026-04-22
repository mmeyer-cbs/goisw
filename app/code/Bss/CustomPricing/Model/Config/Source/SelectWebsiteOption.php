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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\Config\Source;

use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Retrieve website data to array option
 */
class SelectWebsiteOption implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * SelectWebsiteOption constructor.
     *
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->mappedWebsiteData();
    }

    /**
     * Mapping website data to array
     *
     * @return array
     */
    public function mappedWebsiteData()
    {
        $websites = $this->getWebsites();
        $mappedWebsitesData = [];
        foreach ($websites as $website) {
            if ($website->getId() != 0) {
                $mappedWebsitesData[] = [
                    "value" => (int) $website->getId(),
                    "label" => $website->getName()
                ];
            }
        }
        return $mappedWebsitesData;
    }

    /**
     * Get list website
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected function getWebsites()
    {
        return $this->websiteRepository->getList();
    }
}
