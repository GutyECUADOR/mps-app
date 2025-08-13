<?php

namespace App\Console\Commands;

use App\Models\WooCommerceOrder;
use App\Services\ApiWooCommerceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchApiDataWooCommerce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-api-data-woo-commerce';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta las ordernes de WooCommerce desde la API externa y las almacena en la base de datos local.';

    /**
     * Execute the console command.
     */
    public function handle(ApiWooCommerceService $apiWooCommerceService): void
    {
        $this->info('Iniciando la consulta de datos de WooCommerce...');

        // Llamamos al servicio para obtener los datos
        $ordersData = $apiWooCommerceService->fetchExternalData();
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/ApiWooCommerceService.log'),
        ])->info(json_encode($ordersData));

        if ($ordersData) {
            foreach ($ordersData as $orderData) {
                // Renombramos 'id' a 'woocommerce_id' para que coincida con nuestra columna
                $orderData['woocommerce_id'] = $orderData['id'];
                unset($orderData['id']);
                // Creamos la WooCommerceOrder - CABECERA
                // Solo crear la orden si no existe el woocommerce_id
                if (!WooCommerceOrder::where('woocommerce_id', $orderData['woocommerce_id'])->exists()) {
                    $wooCommerceOrder = WooCommerceOrder::create($orderData);


                    Log::build([
                        'driver' => 'single',
                        'path' => storage_path('logs/ApiWooCommerceService.log'),
                    ])->info('MetaData DNI: '. json_encode($orderData['meta_data'][1]['value']));

                    //Guardar la direccion de envio
                    $billingAddress = $orderData['billing'];

                    // Agregar la llave 'dni' al objeto billing si existe en meta_data
                   
                    $billingAddress['dni'] = $orderData['meta_data'][1]['value'];
                    

                    $wooCommerceOrder->billingAddress()->create($billingAddress);

                    // Creamos la WooCommerceOrder - DETALLE
                    if (isset($orderData['line_items'])) {
                        foreach ($orderData['line_items'] as $itemData) {
                            // Renombramos 'id' del item
                            $itemData['wc_id'] = $itemData['id'];
                            unset($itemData['id']);

                            // Usamos la relación para crear el item.
                            // Laravel asignará automáticamente el 'wc_order_id'.
                            $wooCommerceOrder->lineItems()->create($itemData);
                        }
                    }
                }
            }
            $this->info('Datos obtenidos exitosamente.');
        } else {
            $this->error('No se pudieron obtener los datos de WooCommerce o no existen nuevas ordenes.');
        }

        $this->info('Consulta finalizada.');
    }
}
