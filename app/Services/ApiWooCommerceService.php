<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ApiWooCommerceService
{
    protected $client;

    public function __construct()
    {
        // Inicializamos GuzzleHttp\Client
        $this->client = new Client([
            'base_uri' => env('BASE_URL_API_WOOCOMMERCE')
        ]);
    }

    public function fetchExternalData(): ?array
    {
        try {
            //$currentDate = now()->subMonth()->format('Y-m-d\T00:00:00-05:00'); // ULTIMO MES
            $currentDate = now()->format('Y-m-d\T00:00:00-05:00');
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/ApiWooCommerceService.log'),
            ])->info('Ordenes de WooCommerce hasta la fecha:'.$currentDate);

            $response = $this->client->get("/wp-json/wc/v3/orders?after={$currentDate}&status=completed", [
                'auth' => [
                    env('APP_KEY_API_WOOCOMMERCE'),
                    env('APP_SECRET_API_WOOCOMMERCE')
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }

            return null;
        } catch (RequestException $e) {
            // Registramos el error para poder depurarlo
            Log::error('Error al obtener datos de la API externa: ' . $e->getMessage());
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/errors-ApiWooCommerceService.log'),
            ])->error('Error al obtener datos de la API externa BUILD: '. $e->getMessage());
            return null;
        }
    }
}
