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
namespace Bss\QuoteExtension\CustomerData;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Item pool
 */
class ItemPool implements ItemPoolInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $managerObject;

    /**
     * Default item id
     *
     * @var string
     */
    protected $defaultItemId;

    /**
     * Item map. Key is item type, value is item object id in di
     *
     * @var array
     */
    protected $itemMap;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $managerObject
     * @param string $defaultItemId
     * @param array $itemMap
     * @codeCoverageIgnore
     */
    public function __construct(
        ObjectManagerInterface $managerObject,
        $defaultItemId,
        array $itemMap = []
    ) {
        $this->managerObject = $managerObject;
        $this->defaultItemId = $defaultItemId;
        $this->itemMap = $itemMap;
    }

    /**
     * { @inheritdoc }
     *
     * @codeCoverageIgnore
     * @param Item $item
     * @return array
     * @throws LocalizedException
     */
    public function getItemData(Item $item)
    {
        return $this->get($item->getProductType())->getItemData($item);
    }

    /**
     * Get section source by name
     *
     * @param string $type
     * @return ItemInterface
     * @throws LocalizedException
     */
    protected function get($type)
    {
        $itemId = isset($this->itemMap[$type]) ? $this->itemMap[$type] : $this->defaultItemId;
        $item = $this->managerObject->get($itemId);

        if (!$item instanceof ItemInterface) {
            throw new LocalizedException(
                __('%1 doesn\'t extend \Bss\QuoteExtension\CustomerData\ItemInterface', $type)
            );
        }
        return $item;
    }
}
