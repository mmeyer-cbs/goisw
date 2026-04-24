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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ManageQuote;
use Magento\Sales\Model\Order;

/**
 * Class Edit
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Bss\QuoteExtension\Helper\FormatDate
     */
    protected $formatDateHelper;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\QuoteExtension\Helper\FormatDate $formatDateHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Bss\QuoteExtension\Helper\FormatDate $formatDateHelper,
        array $data = []
    ) {
        $this->coreRegistry  = $registry;
        $this->formatDateHelper = $formatDateHelper;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'entity_id';
        $this->_blockGroup = 'Bss_QuoteExtension';
        $this->_controller = 'adminhtml_quoteExtension';

        parent::_construct();
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->setId('sales_order_view');

        $quoteExtension = $this->getQuote();
        $salesOrder = $this->getMageQuote();
        $this->addButtons($quoteExtension, $salesOrder);
    }

    /**
     * Add buttons
     *
     * @param ManageQuote $quoteExtension
     * @param Order $salesOrder
     */
    public function addButtons($quoteExtension, $salesOrder)
    {
        $this->addButtonBack();

        if ($this->canShowButtonAction($quoteExtension->getStatus())) {
            $this->addButtonSendEmail($quoteExtension, $salesOrder);
            $this->addButtonAgress($quoteExtension, $salesOrder);
            $this->addButtonRejected($quoteExtension, $salesOrder);

            if ($quoteExtension->getStatus() != Status::STATE_REJECTED) {
                if ($salesOrder && $quoteExtension->getCustomerId()) {
                    $this->addButtonConvertOrder($quoteExtension, $salesOrder);
                }
            }
        }
        $this->addButtonAssignCustomer($quoteExtension, $salesOrder);
        $this->addButtonPrint($quoteExtension, $salesOrder);

        $this->addButtonDelete($quoteExtension, $salesOrder);
    }

    /**
     * Add button assign customer to quote
     *
     * @param \Bss\QuoteExtension\Model\Quote $quote
     * @param \Bss\QuoteExtension\Model\Quote $mageQuote
     */
    public function addButtonAssignCustomer($quote, $mageQuote)
    {
        if ($quote->getCustomerIsGuest() || ($quote->getCustomerIsGuest() === null && $quote->getCustomerEmail())) {
            $message = "Are you sure assign customer to quote";
            $this->addButton(
                'assign_customer_to_quote',
                [
                    'label' => __('Assign Customer To Quote'),
                    'class' => 'assign_customer_to_quote action-default',
                    'onclick' => "confirmSetLocation(" . "
                        '{$message}',
                        '{$this->getUrl(
                        'bss_quote_extension/*/assignCustomer',
                            [
                                'entity_id' => $quote->getId(),
                                'quote_id' => $mageQuote->getId(),
                            ]
                    )
                        }'" . "
                    )"
                ]
            );
        }
    }

    /**
     * Add button back
     */
    public function addButtonBack()
    {
        $this->buttonList->update(
            'back',
            'onclick',
            'setLocation(\'' . $this->getUrl('bss_quote_extension/*/index') . '\')'
        );
    }

    /**
     * Add button rejected
     *
     * @param ManageQuote $quoteExtension
     * @param Order $salesOrder
     */
    public function addButtonRejected($quoteExtension, $salesOrder)
    {
        $message = __('Are you sure you want to reject this quote?');
        $this->addButton(
            'rejected',
            [
                'label' => __('Rejected'),
                'class' => 'rejected action-default',
                'onclick' => "confirmSetLocation(" . "
                        '{$message}',
                        '{$this->getUrl(
                    'bss_quote_extension/*/rejected',
                    [
                                'entity_id' => $quoteExtension->getId(),
                                'quote_id' => $salesOrder->getId()
                            ]
                )
                        }'" . "
                    )"
            ]
        );
    }

    /**
     * Add send email
     *
     * @param ManageQuote $quoteExtension
     * @param Order $salesOrder
     */
    public function addButtonSendEmail($quoteExtension, $salesOrder)
    {
        $message = __('Are you sure you want to send a confirmation email to customer?');
        $this->addButton(
            'send_notification',
            [
                'label' => __('Send to Customer'),
                'class' => 'send-email action-default',
                'onclick' => "confirmSetLocation(" . "
                        '{$message}',
                        '{$this->getUrl(
                    'bss_quote_extension/*/sendCustomer',
                    [
                                'entity_id' => $quoteExtension->getId(),
                                'quote_id' => $salesOrder->getId()
                            ]
                )
                        }'" . "
                    )"
            ]
        );
    }

    /**
     * Add button agree
     *
     * @param ManageQuote $quoteExtension
     * @param Order $salesOrder
     */
    public function addButtonAgress($quoteExtension, $salesOrder)
    {
        $message = __('Are you sure you want to complete this quote?');
        $this->addButton(
            'agree_quote',
            [
                'label' => __('Finish Quote'),
                'class' => 'agree_quote action-default',
                'onclick' => "confirmSetLocation(" . "
                        '{$message}',
                        '{$this->getUrl(
                    'bss_quote_extension/*/agree',
                    [
                                'entity_id' => $quoteExtension->getId(),
                                'quote_id' => $salesOrder->getId(),
                            ]
                )
                        }'" . "
                    )"
            ]
        );
    }

    /**
     * Add button order
     *
     * @param ManageQuote $quoteExtension
     * @param Order $salesOrder
     */
    public function addButtonConvertOrder($quoteExtension, $salesOrder)
    {
        $this->addButton(
            'create_order',
            [
                'label' => __('Convert Quote to Order'),
                'class' => 'create_order action-default',
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        'bss_quote_extension/*/createorder',
                        ['entity_id' => $quoteExtension->getId(), 'quote_id' => $salesOrder->getId()]
                    ) . '\')'
            ]
        );
    }

    /**
     * Add button print
     *
     * @param ManageQuote $quoteExtension
     * @param Order $salesOrder
     */
    public function addButtonPrint($quoteExtension, $salesOrder)
    {
        $this->addButton(
            'print',
            [
                'label' => __('Print'),
                'class' => 'print action-default',
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        'bss_quote_extension/*/print',
                        ['entity_id' => $quoteExtension->getId(), 'quote_id' => $salesOrder->getId()]
                    ) . '\')'
            ]
        );
    }

    /**
     * Add button delete when quote status difference ordered
     *
     * @param ManageQuote $quoteExtension
     * @param Order $salesOrder
     */
    public function addButtonDelete($quoteExtension, $salesOrder)
    {
        if ($quoteExtension->getStatus() != Status::STATE_ORDERED) {
            $message = __('Are you sure you want to delete this quote?');
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete Quote'),
                    'class' => 'print action-default',
                    'onclick' => "confirmSetLocation(" . "
                        '{$message}',
                        '{$this->getUrl(
                        'bss_quote_extension/*/delete',
                        [
                                'entity_id' => $quoteExtension->getId(),
                                'quote_id' => $salesOrder->getId(),
                            ]
                    )
                        }'" . "
                    )"
                ]
            );
        }
    }

    /**
     * Retrieve order model object
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('mage_quote');
    }

    /**
     * Retrieve order model object
     *
     * @return Order
     */
    public function getMageQuote()
    {
        return $this->coreRegistry->registry('mage_quote');
    }

    /**
     * Retrieve order model object
     *
     * @return ManageQuote
     */
    public function getQuote()
    {
        return $this->coreRegistry->registry('quoteextension_quote');
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder() ? $this->getOrder()->getId() : null;
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase|string
     * @throws \Exception
     */
    public function getHeaderText()
    {
        $_extOrderId = $this->getOrder()->getExtOrderId();
        if ($_extOrderId) {
            $_extOrderId = '[' . $_extOrderId . '] ';
        } else {
            $_extOrderId = '';
        }
        return __(
            'Quote # %1 %2 | %3',
            $this->getOrder()->getRealOrderId(),
            $_extOrderId,
            $this->formatDate(
                $this->_localeDate->date($this->formatDateHelper->getNewDate($this->getOrder()->getCreatedAt())),
                \IntlDateFormatter::MEDIUM,
                true
            )
        );
    }

    /**
     * URL getter
     *
     * @param string $params
     * @param array $params2
     * @return string
     */
    public function getUrl($params = '', $params2 = [])
    {
        $params2['quote_id'] = $this->getOrderId();
        return parent::getUrl($params, $params2);
    }

    /**
     * Return back url for view grid
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getOrder() && $this->getOrder()->getBackUrl()) {
            return $this->getOrder()->getBackUrl();
        }

        return $this->getUrl('bss_quote_extension/*/');
    }

    /**
     * Return back can show button action.
     *
     * @param string $quoteStatus
     * @return bool
     */
    protected function canShowButtonAction($quoteStatus)
    {
        $ignore = [
            Status::STATE_CANCELED,
            Status::STATE_ORDERED ,
            Status::STATE_REJECTED,
            Status::STATE_UPDATED,
            Status::STATE_COMPLETE,
            Status::STATE_EXPIRED
        ];
        if (in_array($quoteStatus, $ignore)) {
            $this->buttonList->remove('agree_quote');
            $this->buttonList->remove('save');
            return false;
        }
        return true;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
