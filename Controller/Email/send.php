<?php
namespace Atix\PaymentGateway\Controller\Email;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

class Send extends Action
{
    protected $invoiceSender;
    protected $resultFactory;

    public function __construct(
        Context $context,
        InvoiceSender $invoiceSender,
        ResultFactory $resultFactory
    ) {
        $this->invoiceSender = $invoiceSender;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('orderId');
    
        // Load order by increment id (change '100000001' to your order increment id)
        $order = $this->_objectManager->create(Order::class)->loadByIncrementId($orderId);
        // var_dump($order->getIncrementId());
        // return;

        // Check if the order can be invoiced
        if ($order->canInvoice()) {
            // Create invoice
            $invoice = $this->_objectManager->create(\Magento\Sales\Model\Service\InvoiceService::class)->prepareInvoice($order);
            $invoice->register();
            $invoice->save();

            // Send payment confirmation email
            $this->invoiceSender->send($invoice);

            $this->messageManager->addSuccessMessage(__('Payment confirmation email sent.'));
        } else {
            $this->messageManager->addErrorMessage(__('Cannot create invoice.'));
        }

        // Redirect to a relevant page
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}