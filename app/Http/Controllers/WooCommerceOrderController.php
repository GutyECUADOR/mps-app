<?php

namespace App\Http\Controllers;

use App\Models\WooCommerceOrder;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class WooCommerceOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $client = new Client([
            'base_uri' => env('BASE_URL_API_WOOCOMMERCE'),
        ]);

        $response = $client->get('/wp-json/wc/v3/orders?after=2025-05-05T00:00:00-05:00', [
            'auth' => [
                env('APP_KEY_API_WOOCOMMERCE'),
                env('APP_SECRET_API_WOOCOMMERCE')
            ]
        ]);

        // Obtener el cuerpo de la respuesta
        $body = $response->getBody();
        $result = json_decode($body, true);

        dd($result);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(WooCommerceOrder $wooCommerceOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WooCommerceOrder $wooCommerceOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WooCommerceOrder $wooCommerceOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WooCommerceOrder $wooCommerceOrder)
    {
        //
    }
}
