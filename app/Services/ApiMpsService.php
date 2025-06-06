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

        $wc_orders = WooCommerceOrder::where('status', 'completed')
            ->whereNull('procesing_mps_date')
            ->get();

        // Mapeo de WooCommerceOrder a Pedido
        $pedidos = $wc_orders->map(function ($order) {
            return new Pedido([
                'AccountNum'           => $order->customer_id,
                'NombreClienteEntrega' => $order->shipping_name ?? $order->billing_name ?? $order->customer_name,
                'ClienteEntrega'       => $order->shipping_address_1 ?? $order->billing_address_1,
                'TelefonoEntrega'      => $order->shipping_phone ?? $order->billing_phone ?? $order->customer_phone,
                'DireccionEntrega'     => $order->shipping_address_1 ?? $order->billing_address_1,
            ]);
        });

        dd($wc_orders);
        return null;

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


            /* GET Orders and  */

            // Consulta de lista de marcas
            $response = $this->client->post('/api/WebApi/RealizarPedido', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token, // Set the Authorization header
                    'Accept'        => 'application/json', // Optional: Specify that you expect JSON response
                ],
            ]);

            // Get productos
            $body = $response->getBody();
            $response = json_decode($body, true);

            return null;
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
