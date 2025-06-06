<?php

namespace App\Console\Commands;

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
        $data = $apiWooCommerceService->fetchExternalData();
        Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/ApiWooCommerceService.log'),
            ])->info(json_encode($data));

        if ($data) {
            $this->info('Datos obtenidos exitosamente.');
            

        } else {
            $this->error('No se pudieron obtener los datos de WooCommerce o no existen nuevas ordenes.');
        }

        $this->info('Consulta finalizada.');
    }
}
