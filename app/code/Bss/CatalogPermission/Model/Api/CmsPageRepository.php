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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CatalogPermission\Model\Api;

/**
 * Save permission in cms page to db
 */
class CmsPageRepository implements \Bss\CatalogPermission\Api\CmsPageRepositoryInterface
{
    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    protected $pageRepository;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Construct
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Cms\Model\PageRepository $pageRepository
     */
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Cms\Model\PageRepository                $pageRepository
    ) {
        $this->serializer = $serializer;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Save cms page permission by REST API
     *
     * @inheritdoc
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($page)
    {
        return $this->pageRepository->save($page);
    }
}
