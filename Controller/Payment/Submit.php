<?php
namespace Atix\PaymentGateway\Controller\Payment;

use Atix\PaymentGateway\Model\PaymentGatewayModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Quote\Model\QuoteFactory;

const BASE_URL_SANDBOX = "https://gateway.atix.com.pe/PaymentGatewayJWS_Sandbox/Service1.svc";
const BASE_URL_PROD = "https://gateway.atix.com.pe/PaymentGatewayJWS/Service1.svc";

class Submit extends Action
{
    protected $jsonResultFactory;
    protected $customerSession;
    protected $paymentMethod;

    protected $quoteFactory;
    protected $orderFactory;
    protected $quoteManagement;
    protected $customerFactory;
    protected $productFactory;
    protected $currencyFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        Session $customerSession,
        PaymentGatewayModel $paymentMethod,
        QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        CurrencyFactory $currencyFactory
    ) {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->customerSession = $customerSession;
        $this->paymentMethod = $paymentMethod;
        $this->quoteFactory = $quoteFactory; 
        $this->orderFactory = $orderFactory;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->productFactory = $productFactory;
        $this->currencyFactory = $currencyFactory;
    }

    public function execute()
    {
        // Obtenemos el 'debug' - modo de prueba desde la configuración del plugin
        $debug = $this->paymentMethod->getConfigData('debug');
        // Por defecto será la url de SANBOX
        $url = BASE_URL_SANDBOX . '/GBCPE_AuthenticateUser';
        // Si no es de prueba, estamos en producción y la url será la siguiente
        if(!$debug){
            $url =  BASE_URL_PROD . '/GBCPE_AuthenticateUser';
        } 
        // Obtenemos los parámetros del query
        $queryParams = $this->getRequest()->getParams();
        // Creamos la manejador de respuesta del endpoint
        $result = $this->jsonResultFactory->create();

        try {

            // Buscamos el detalle del carrito de compra
            $quote = $this->quoteFactory->create()->load((int)$queryParams['quoteId']);
          
            // Verificamos que el Código de moneda del carrito de compra debe estar habilitado
            if(!$this->checkCurrency($quote->getBaseCurrencyCode()))
            {
                throw new \Exception(__('Currency is not allowed'));
                return;
            }

            $cartManagement = $this->_objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
            $orderRepository = $this->_objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
            
            // Creamos el pedido por medio del quote
            // $orderData = $this->quoteManagement->submit($quote);
            $orderId = $cartManagement->placeOrder($quote->getId());
            $order = $orderRepository->get($orderId);
            // dd($order->getIncrementId());

            // Si se creo el pedido
            if ($order) {
                // Obtenemos el identificador del pedido y lo guardamos el la sesión de orderId
                $orderId = $order->getIncrementId();
                $this->customerSession->setData('orderId', $orderId);
                // Creamos la respuesta del endpoint con los datos necesarios para generar el link de pago
                $response = [
                    'success' => true, 
                    'message' => __('Payment link was generated successful.'), 
                    'order_id' => $orderId,
                    'api_key' => $this->getApiKeyByCurrency($order->getOrderCurrencyCode()),
                    'url' => $url
                ];
            } else {
                // Creamos la respuesta si el proceso del pago fallo
                $response = [
                    'success' => false, 
                    'message' => __('Payment processing failed.'), 
                ];
            }
        } catch (\Exception $e) {
            // Almacenamos el error del servidor o desconocido
            $response['message'] = $e->getMessage();
        }

        // Seteamos la respuesta y lo retornamos en el endpoint
        $result->setData($response);
        return $result;
    }

    public function getApiKeyByCurrency(String $currency): String {
        // Obtenemos el api_key desde la configuración del plugin
        if($currency === 'PEN')
            return $this->paymentMethod->getConfigData('merchant_gateway_api_key_pen');
        else if($currency === 'USD')
            return $this->paymentMethod->getConfigData('merchant_gateway_api_key_usd');

        throw new \Exception('Token not provided');
    }
    
    public function checkCurrency(String $currency)
    {
        $availableCurrencies = $this->currencyFactory->create()->getConfigAllowCurrencies();
        if (is_array($availableCurrencies)) {
            foreach ($availableCurrencies as $currency_value) {
                if ($currency == $currency_value){
                    return true;
                }
            }
        }
        return false;
    }
}
