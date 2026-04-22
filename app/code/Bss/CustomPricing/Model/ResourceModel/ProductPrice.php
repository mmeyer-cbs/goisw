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
namespace Bss\CustomPricing\Model\ResourceModel;

use Magento\Customer\Controller\Adminhtml\Group\Delete;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address\DeleteRelation;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Address
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductPrice extends AbstractDb
{
    const TABLE = 'bss_product_price';
    const PRIMARY_KEY = 'id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, self::PRIMARY_KEY);
    }

    /**
     * Is product validate to rule id
     *
     * @param int $ruleId
     * @param int|null $productId
     * @param \Bss\CustomPricing\Model\ProductPrice|null $productPrice
     * @return $this|false|array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function loadBy($ruleId = null, $productId = null, $productPrice = null)
    {
        try {
            $connection = $this->getConnection();
            $select = $connection->select();
            $select->from($this->getMainTable(), "id");
            if ($productId === null && $ruleId) {
                $select->where("rule_id = ?", $ruleId);
            }
            if ($ruleId === null && $productId) {
                $select->where("product_id = ?", $productId);
            }
            if (!$ruleId || !$productId) {
                $result = $connection->fetchAll($select);
                if ($result) {
                    $idx = array_column($result, 'id');
                }
                return $idx ?? [];
            }
            $select = $connection->select()->from($this->getMainTable(), "*");
            $select->where(sprintf(
                "rule_id = %s AND product_id = %s",
                $ruleId,
                $productId
            ));
            $data = $connection->fetchRow($select);

            if ($data) {
                $productPrice->setData($data);
            }

            $this->_afterLoad($productPrice);
            $productPrice->afterLoad();
            $productPrice->setOrigData();
            $productPrice->setHasDataChanges(false);

            return $this;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * The customer id has applied on rule
     *
     * @param int $ruleId
     * @param int $pId
     * @return bool|string
     */
    public function hasProduct($ruleId, $pId)
    {
        try {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getMainTable());
            $select->where(sprintf("rule_id = %s AND product_id = %s", $ruleId, $pId))
                ->limit(1);
            return $connection->fetchOne($select);
        } catch (\Exception $e) {
            return false;
        }
    }
}
