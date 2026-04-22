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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceSequenceMeta;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * SequenceStore
 */
class SequenceStore
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreRepositoryInterface
     */
    private $repository;

    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var Config
     */
    private $sequenceConfig;

    /**
     * @var ResourceSequenceMeta
     */
    private $resourceSequenceMeta;

    /**
     * Construct.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param Builder $sequenceBuilder
     * @param StoreRepositoryInterface $repository
     * @param Config $sequenceConfig
     * @param ResourceSequenceMeta $resourceSequenceMeta
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Builder $sequenceBuilder,
        StoreRepositoryInterface $repository,
        Config $sequenceConfig,
        ResourceSequenceMeta $resourceSequenceMeta
    ) {
        $this->logger = $logger;
        $this->sequenceBuilder = $sequenceBuilder;
        $this->repository = $repository;
        $this->sequenceConfig = $sequenceConfig;
        $this->resourceSequenceMeta = $resourceSequenceMeta;
    }

    /**
     * Create sequence with metadata and profile: quoteExtension
     * When admin create storeView before setup module or disable module QuoteExtension
     */
    public function addSequence()
    {
        $stores = $this->repository->getList();
        foreach ($stores as $store) {
            $storeId = $store->getId();
            try {
                $meta = $this->resourceSequenceMeta->loadByEntityTypeAndStore(
                    'quote_extension',
                    $storeId
                );
                if (!$meta->getId()) {
                    $this->sequenceBuilder->setPrefix($storeId)
                        ->setSuffix($this->sequenceConfig->get('suffix'))
                        ->setStartValue($this->sequenceConfig->get('startValue'))
                        ->setStoreId($storeId)
                        ->setStep($this->sequenceConfig->get('step'))
                        ->setWarningValue($this->sequenceConfig->get('warningValue'))
                        ->setMaxValue($this->sequenceConfig->get('maxValue'))
                        ->setEntityType('quote_extension')
                        ->create();
                }
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }

        }
    }
}
