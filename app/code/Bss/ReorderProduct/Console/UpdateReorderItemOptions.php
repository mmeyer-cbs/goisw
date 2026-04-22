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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReorderProduct\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateReorderItemOptions
 *
 * @package Bss\ReorderProduct\Controller\Cart
 */
class UpdateReorderItemOptions extends Command
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Bss\ReorderProduct\Model\ResourceModel\SaveReorderItemOptions
     */
    protected $saveReorderItemOptions;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * @var \Bss\ReorderProduct\Model\SaveItemOptions
     */
    protected $saveItemOptions;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * UpdateReorderItemOptions constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Bss\ReorderProduct\Model\ResourceModel\SaveReorderItemOptions $saveReorderItemOptions
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Bss\ReorderProduct\Model\SaveItemOptions $saveItemOptions
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $name
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Bss\ReorderProduct\Model\ResourceModel\SaveReorderItemOptions $saveReorderItemOptions,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Bss\ReorderProduct\Model\SaveItemOptions $saveItemOptions,
        \Psr\Log\LoggerInterface $logger,
        $name = null
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->saveReorderItemOptions = $saveReorderItemOptions;
        $this->serializer = $serializer;
        $this->saveItemOptions = $saveItemOptions;
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('bss:reorder:update-item-options');
        $this->setDescription('Colect and update item options for reorder product');

        parent::configure();
    }

    /**
     * Execute function
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->orderCollectionFactory->create()->addAttributeToSelect('*');
        if ($collection->getSize()) {
            $this->saveReorderItemOptions->deleteAllRow();
            foreach ($collection as $order) {
                $this->saveItemOptions->processData($order);
                $output->writeln("Update order: " . $order->getIncrementId());
            }
        }
        return $this;
    }
}
