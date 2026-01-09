<?php
namespace Atix\PaymentGateway\Controller\Payment;

use Atix\PaymentGateway\Model\PaymentGatewayModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

const BASE_URL_SANDBOX = "https://gateway.atix.com.pe/PaymentGatewayJWS_Sandbox/Service1.svc";
const BASE_URL_PROD = "https://gateway.atix.com.pe/PaymentGatewayJWS/Service1.svc";

class Confirmation extends Action
{
    protected $jsonResultFactory;
    protected $customerSession;
    protected $paymentMethod;
    protected $orderFactory;
    protected $orderSender;
    protected $resultRedirectFactory;
    protected $invoiceSender;

    public function __construct(
        Context $context,
        Session $customerSession,
        PaymentGatewayModel $paymentMethod,
        OrderFactory $orderFactory,
        OrderSender $orderSender,
        RedirectFactory $resultRedirectFactory,
        InvoiceSender $invoiceSender
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->paymentMethod = $paymentMethod;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->invoiceSender = $invoiceSender;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // Obtenemos el token del parámetro tk 
            $token = $this->getRequest()->getParam('tk');
            // Si no existe retornamos el error: Token no proporcionado
            if (!$token) {
                throw new \Exception(__('Token not provided'));
            }
            $debug = $this->paymentMethod->getConfigData('debug');
            // Por defecto será la url de SANBOX
            $url = BASE_URL_SANDBOX . '/GBCPE_ResultTransaction';
            // Si es producción la url será la siguiente
            if(!$debug){
                $url = BASE_URL_PROD . '/GBCPE_ResultTransaction';
            } 
            
            // Obtener el identificador del pedido actual, de la sesión orderId
            $orderId = $this->customerSession->getData('orderId');
            // $order = $this->orderFactory->create()->load("000000009");
            $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($orderId);
            
            // Validamos si existe el pedido
            if (!$order->getId()) {
                throw new \Exception(__('Order not found'));
            }
            
            // Verificamos el pago en Servicio de pago Atix, mediante el token
            $result = $this->api_curl_request_urlencode('{"Token":"'.$token.'"}', $url);
            $response = json_decode($result);
            $resultCode = $response[0]->ResultCode;
            
            

             // Si el código de la consulta es '00' 
            // Se realizó el pago en el API de Atix Paymente Service
            if (isset($response[0]->ResultCode) && $resultCode === '00') {
                // Cambiamos el estado de la orden a Complete y guardamos.
                $order->setStatus('complete');
                $order->save();
                // enviamos el correo de la factura del pedido al cliente 
                // $this->orderSender->send($order);
                if ($order->canInvoice()) {
                    // Create invoice
                    $invoice = $this->_objectManager->create(\Magento\Sales\Model\Service\InvoiceService::class)->prepareInvoice($order);
                    $invoice->register();
                    $invoice->save();
                    
                    // Send payment confirmation email
                    $this->invoiceSender->send($invoice);
                    $this->emptyCart();
                }
                $this->emptyCart();
                
                // Agregamos el mensaje al cliente con el pago se confirmo correctamente y desde i18n dependiendo al idioma de la tienda
                $this->messageManager->addSuccessMessage(__('The payment was confirmed successfully. For your order ').$order->getIncrementId().'. '.__('Visit your order list or email inbox for more details.'));

                // Eliminamos el pedido de la sesión orderId
                $this->customerSession->start();
                $this->customerSession->setData('orderId', null);
                // Redirigimos a la página de confirmación del pedido
                // return $resultRedirect->setPath('checkout/onepage/success');
                return $resultRedirect->setPath('checkout/cart');
            } else {
                // Cambiamos el estado a closed (Cerrado) y guardamos
                $order->setStatus('closed'); // closed 
                $order->save();
                $this->emptyCart();
                
                // Agregamos el mensaje al cliente con el pago fallido 
                $this->messageManager->addError(__('Payment failed. Please try to place your order again later.'));
                // Redirigimos al carrito 
                return $resultRedirect->setPath('checkout/cart');
            }
        } catch (\Exception $e) {
            // Si existe un error del servidor o desconocido
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('checkout/cart');
        }
    }

    public function api_curl_request_urlencode($payload, $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    public function emptyCart() {
        $checkoutSession = $this->_objectManager->get(\Magento\Checkout\Model\Session::class);
        $quoteId = $checkoutSession->getQuote()->getId();
        
        $this->_objectManager->create('Magento\Quote\Model\Quote')
            ->load($quoteId)
            ->delete();
    }
}
