<?php

namespace App\Services;

use App\Models\Pedido;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Models\WooCommerceOrder;

class ApiMpsService
{
    protected $client;

    public function __construct()
    {
        // Inicializamos GuzzleHttp\Client
        $this->client = new Client([
            'base_uri' => env('BASE_URL_API_SHOP'),
            'timeout'  => 5.0,
        ]);
    }

    public function createOrders()
    {
        $wc_orders = WooCommerceOrder::with(['billingAddress', 'lineItems'])
            ->where('status', 'completed')
            ->whereNull('processing_mps_date')
            ->get();

        // Mapeo de WooCommerceOrder a Pedido
        $listaPedidos = $wc_orders->map(function ($order) {
            $listaPedidoDetalle = $order->lineItems->map(function ($item) {

                // Consulta a API de WooCommerce para obtener detalles del producto
                $productDetails = null;
                try {
                    $wcClient = new Client([
                        'base_uri' => env('BASE_URL_API_WOOCOMMERCE'),
                        'timeout'  => 5.0,
                        'auth'     => [env('APP_KEY_API_WOOCOMMERCE'), env('APP_SECRET_API_WOOCOMMERCE')],
                    ]);
                    $response = $wcClient->get("/wp-json/wc/v3/products/{$item->product_id}");
                    $productDetails = json_decode($response->getBody(), true);

                    Log::build([
                        'driver' => 'single',
                        'path' => storage_path('logs/ApiWooCommerceService.log'),
                    ])->info('Marca producto consultado: ' . json_encode($productDetails['brands'][0]['name'] ?? 'No disponible'));
                } catch (\Exception $e) {
                    Log::error('Error al consultar producto WooCommerce: ' . $e->getMessage());
                }

                return [
                    'PartNum'  => $item->sku,
                    'Cantidad' => $item->quantity,
                    // Asegúrate de tener un valor por defecto si la marca no existe para evitar errores
                    'Marks'    => $productDetails['brands'][0]['name'] ?? 'N/A',
                    'Bodega'   => 'BCOTA',
                ];
            })->toArray();

            // Devuelve la estructura de un único pedido
            return [
                'AccountNum'           => '79580718', // Numero de Cuenta
                'NombreClienteEntrega' => $order->billingAddress->first_name . ' ' . $order->billingAddress->last_name,
                'ClienteEntrega'       => '79580718', // Numero de DNI
                'TelefonoEntrega'      => $order->billingAddress->phone,
                'DireccionEntrega'     => $order->billingAddress->address_1 . ' ' . $order->billingAddress->address_2,
                'StateId'              => $order->billingAddress->state,
                'CountyId'             => $order->billingAddress->city,
                'RecogerEnSitio'       => 0,
                'EntregaUsuarioFinal'  => 0,
                'dlvTerm'              => '',
                'dlvmode'              => '',
                'Observaciones'        => 'Pedido Web Nro. ' . $order->woocommerce_id,
                'listaPedidoDetalle'   => $listaPedidoDetalle // El detalle ahora está anidado aquí
            ];
        });

        // Construye el objeto final en el formato deseado
        $objectResponse = [
            'listaPedido' => $listaPedidos->toArray()
        ];

        // Log de pedidos
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/ApiWooCommerceService.log'),
        ])->info('Pedidos enviados a MPS: ' . json_encode($objectResponse));

        try {

            $response = $this->client->post('/Token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cache-Control' => 'no-cache',
                ],
                'form_params' => [
                    'grant_type' => 'password',
                    'username'   => env('CLIENT_ID_MPS_USER'),
                    'password'   => env('CLIENT_SECRET_MPS_PASSWORD'),
                ],
            ]);

            // Obtener el cuerpo de la respuesta
            $body = $response->getBody();
            $result = json_decode($body, true);
            $token = $result['access_token']; // Get Access Token

            $response = $this->client->post('/api/WebApi/RealizarPedido', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token, // Set the Authorization header
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'json' => $objectResponse, // Envía el objeto JSON correctamente estructurado
            ]);

            // Get productos
            $body = $response->getBody();
            $response = json_decode($body, true);

            return $response;
        } catch (RequestException $e) {
            // Registramos el error para poder depurarlo
            Log::error('Error al obtener datos de la API externa: ' . $e->getMessage());
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/errors-ApiWooCommerceService.log'),
            ])->error('Error al obtener datos de la API externa BUILD: ' . $e->getMessage());
            return null;
        }
    }
}
