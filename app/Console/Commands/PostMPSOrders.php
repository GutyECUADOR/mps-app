<?php

namespace App\Console\Commands;

use App\Services\ApiMpsService;
use Illuminate\Console\Command;

class PostMPSOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-mps-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea las órdenes mediante el API de MPS almacenadas en la base de datos local.';

    /**
     * Execute the console command.
     */
    public function handle(ApiMpsService $apiMpsService): void
    {
        $this->info('Iniciando la creación de órdenes en MPS...');

        // Llamamos al servicio para crear las órdenes
        $response = $apiMpsService->createOrders();

        if ($response) {
            $this->info('Órdenes creadas exitosamente en MPS.');
        } else {
            $this->error('Error al crear las órdenes en MPS.');
        }
    }
    
}
