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
        $pedidos = $wc_orders->map(function ($order) {
            return new Pedido([
                'AccountNum'           => '1600505505', // Numero de DNI
                'NombreClienteEntrega' => $order->billingAddress->first_name . ' ' . $order->billingAddress->last_name,
                'ClienteEntrega'       => '1600505505', // Numero de DNI
                'TelefonoEntrega'      => $order->billingAddress->phone,
                'DireccionEntrega'     => $order->billingAddress->address_1 . ' ' . $order->billingAddress->address_2,
                'StateId'              => $order->billingAddress->state,
                'CountyId'             => $order->billingAddress->city,
                'RecogerEnSitio'       => 0, // Asumimos que no se recoge en sitio
                'EntregaUsuarioFinal'  => 0, // Asumimos que no hay entrega a usuario final
                'dlvTerm'              => '',
                'dlvmode'              => '',  
                'Observaciones'        => 'Pedido Web Nro. '.$order->woocommerce_id,
            ]);
        });

        dd($pedidos);
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
