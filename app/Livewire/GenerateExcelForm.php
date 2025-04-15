<?php

namespace App\Livewire;

use Livewire\Component;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class GenerateExcelForm extends Component
{
    public $isSubmitting = false;

    // Método que se ejecutará cuando el enlace sea clickeado
    public function handleClick()
    {
        $this->isSubmitting = true;

        // Inicializar el cliente HTTP Guzzle
        $client = new Client([
            'base_uri' => 'https://shopcommerce.mps.com.co:7965',
        ]);

        $response = $client->post('/Token', [
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

        // Consulta de lista de marcas
        $response = $client->post('/api/Webapi/VerCatalogo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token, // Set the Authorization header
                'Accept'        => 'application/json', // Optional: Specify that you expect JSON response
            ],
        ]);

        // Get productos
        $body = $response->getBody();
        $response = json_decode($body, true);

        $listaproductos = $response["listaproductos"];
        //dd($array_productos_bymarca);

        //dd($array_productos_bymarca[0][0]["precios"]["precio_especial"]);
        //dd($array_productos_bymarca[0][0]["categorias"][0]["nombre"]);
        //break; // Detiene el bucle después de la primera iteración FOR TESTS

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = array_keys($listaproductos[0]);
        $sheet->fromArray($headers, NULL, 'A1');

        // Escribir los datos de cada producto
        $rowIndex = 2;
        foreach ($listaproductos as $producto) {
            // Convertir campos anidados a JSON string o algo legible
            foreach ($producto as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $producto[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
            }
            $sheet->fromArray(array_values($producto), NULL, 'A' . $rowIndex);
            $rowIndex++;
        }

        session()->flash('message', '¡El archivo se ha generado correctamente!');
        $this->isSubmitting = false;

        $fileName = 'productos.xlsx';
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path($fileName);
        $writer->save($filePath);

        // Retornar el archivo como respuesta para descargar
        return Response::download($filePath)->deleteFileAfterSend(true);

        // Success

    }


    public function render()
    {
        return view('livewire.generate-excel-form');
    }
}
