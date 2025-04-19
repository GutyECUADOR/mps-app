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
        $activeWorksheet = $spreadsheet->getActiveSheet();
        // Encabezados
        $activeWorksheet->setCellValue('A1', 'PARTNUM');
        $activeWorksheet->setCellValue('B1', 'IDCATEGORIA');
        $activeWorksheet->setCellValue('C1', 'CATEGORIA');
        $activeWorksheet->setCellValue('D1', 'IDSUBCATEGORIA');
        $activeWorksheet->setCellValue('E1', 'SUBCATEGORIA');
        $activeWorksheet->setCellValue('F1', 'TITULO');
        $activeWorksheet->setCellValue('G1', 'DESCRIPCION');
        $activeWorksheet->setCellValue('H1', 'MARCA');
        $activeWorksheet->setCellValue('I1', 'MARCAHOMOLOGADA');
        $activeWorksheet->setCellValue('J1', 'OFERTAPRECIOMINIMO');
        $activeWorksheet->setCellValue('K1', 'OFERTAPRECIOMAXIMO');
        $activeWorksheet->setCellValue('L1', 'PRECIO');
        $activeWorksheet->setCellValue('M1', 'MONEDA');
        $activeWorksheet->setCellValue('N1', 'CANTIDAD');
        $activeWorksheet->setCellValue('O1', 'ARRAYBODEGA');
        $activeWorksheet->setCellValue('P1', 'XMLATRIBUTOS');
        $activeWorksheet->setCellValue('Q1', 'IMAGENES');
        $activeWorksheet->setCellValue('R1', 'ETIQUETAS');

        /* foreach ($activeWorksheet->getColumnIterator() as $column) {
            $activeWorksheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
 */
        $activeWorksheet->getStyle('A1:R1')->getFont()->setBold(true);

        $row = 2;
        foreach ($listaproductos as $producto) {

            $activeWorksheet->setCellValue('A'.$row, $producto['PartNum']);
            $activeWorksheet->setCellValue('B'.$row, $producto['IdFamilia']);
            $activeWorksheet->setCellValue('C'.$row, $producto['Familia']);
            $activeWorksheet->setCellValue('D'.$row, $producto['IdCategoria']);
            $activeWorksheet->setCellValue('E'.$row, $producto['Categoria']);
            $activeWorksheet->setCellValue('F'.$row, $producto['Name']); // Titulo
            $activeWorksheet->setCellValue('G'.$row, $producto['Description']);
            $activeWorksheet->setCellValue('H'.$row, $producto['Marks']);
            $activeWorksheet->setCellValue('I'.$row, $producto['MarcaHomologada']);
            $activeWorksheet->setCellValue('J'.$row, $producto['Salesminprice']);
            $activeWorksheet->setCellValue('K'.$row, $producto['Salesmaxprice']);
            $activeWorksheet->setCellValue('L'.$row, $producto['precio']);
            $activeWorksheet->setCellValue('M'.$row, $producto['CurrencyDef']);
            $activeWorksheet->setCellValue('N'.$row, $producto['Quantity']);
            $activeWorksheet->setCellValue('O'.$row, json_encode($producto['ListaProductosBodega'], JSON_UNESCAPED_SLASHES));
            $activeWorksheet->setCellValue('P'.$row, $producto['xmlAttributes']);
            $activeWorksheet->setCellValue('Q'.$row, json_encode($producto['Imagenes'], JSON_UNESCAPED_SLASHES));
            $activeWorksheet->setCellValue('R'.$row, $producto['slug']);

           /*  $activeWorksheet->setCellValue('I'.$row, number_format($producto['precios']['precio_especial'] ?? 0, 2, ',') );
           */
            $row++;

        }

        session()->flash('message', '¡El archivo se ha generado correctamente!');
        $this->isSubmitting = false;

        $fileName = 'API_Tixore_final.xlsx';
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
