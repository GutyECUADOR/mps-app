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
            'base_uri' => env('BASE_URL_API_SHOP')
        ]);
    }

    public function createOrders()
    {
        $wc_orders = WooCommerceOrder::with(['billingAddress', 'lineItems'])
            ->where('status', 'completed')
            ->whereNull('processing_mps_date')
            ->get();

        $API_responses = [];

        try {
            // Obtener token solo una vez
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

            $body = $response->getBody();
            $result = json_decode($body, true);
            $token = $result['access_token'];

            foreach ($wc_orders as $order) {

                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/ApiWooCommerceService.log'),
                ])->info('Orden seleccionada: ' . json_encode($order));

                $listaPedidoDetalle = $order->lineItems->map(function ($item) {
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
                        'Marks'    => $productDetails['brands'][0]['name'] ?? 'N/A',
                        'Bodega'   => 'BCOTA',
                    ];
                })->toArray();

                $pedido = [
                    'AccountNum'           => '79580718',
                    'NombreClienteEntrega' => $order->billingAddress->first_name . ' ' . $order->billingAddress->last_name,
                    'ClienteEntrega'       => '79580718',
                    'TelefonoEntrega'      => $order->billingAddress->phone,
                    'DireccionEntrega'     => $order->billingAddress->address_1 . ' ' . $order->billingAddress->address_2,
                    'StateId'              => $order->billingAddress->state,
                    'CountyId'             => $order->billingAddress->city,
                    'RecogerEnSitio'       => 0,
                    'EntregaUsuarioFinal'  => 0,
                    'dlvTerm'              => '',
                    'dlvmode'              => '',
                    'Observaciones'        => 'Pedido Web Nro. ' . $order->woocommerce_id,
                    'listaPedidoDetalle'   => $listaPedidoDetalle
                ];

                $objectRequest = [
                    'listaPedido' => [$pedido]
                ];

                /* PEDIDO ENVIADO A MPS */
                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/ApiWooCommerceService.log'),
                ])->info('Pedido enviado a MPS: ' . json_encode($objectRequest));

                try {
                    $response = $this->client->post('/api/WebApi/RealizarPedido', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                            'Accept'        => 'application/json',
                            'Content-Type'  => 'application/json',
                        ],
                        'json' => $objectRequest,
                    ]);

                    $body = $response->getBody();
                    $apiResponse = json_decode($body, true);

                    Log::build([
                        'driver' => 'single',
                        'path' => storage_path('logs/ApiWooCommerceService.log'),
                    ])->info('Status 200: ' . json_encode($order));

                    // Si la respuesta es 200 OK y contiene "valor": "0", actualiza la columna
                    if ($response->getStatusCode() === 200 && isset($apiResponse['valor']) && $apiResponse['valor'] == "0") {
                        Log::build([
                            'driver' => 'single',
                            'path' => storage_path('logs/ApiWooCommerceService.log'),
                        ])->info('Orden actualizada: ' . json_encode($order));

                        $order->processing_mps_date = now();
                        $order->save();
                    }

                    // Agrega el id de la orden al apiResponse
                    $apiResponse['order_id'] = $order->id;
                    $apiResponse['woocomerce_idwoocommerce_id'] = $order->woocommerce_id;

                    $API_responses[] = $apiResponse;
                } catch (RequestException $e) {
                    Log::error('Error al enviar pedido a MPS: ' . $e->getMessage());
                    Log::build([
                        'driver' => 'single',
                        'path' => storage_path('logs/errors-ApiWooCommerceService.log'),
                    ])->error('Error al enviar pedido a MPS BUILD: ' . $e->getMessage());
                    $API_responses[] = ['error' => $e->getMessage()];
                }
            }
        } catch (RequestException $e) {
            Log::error('Error al obtener token de la API externa: ' . $e->getMessage());
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/errors-ApiWooCommerceService.log'),
            ])->error('Error al obtener token de la API externa BUILD: ' . $e->getMessage());
            return null;
        }

        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/ApiWooCommerceService.log'),
        ])->info('Respuestas de MPS Crear Ordenes: ' . json_encode($API_responses));

        return $API_responses;
    }
}
