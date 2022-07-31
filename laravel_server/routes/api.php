<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\AkeneoProduct;
use App\Http\Resources\AkeneoProductResource;
use App\Http\Resources\AkeneoProductCollection;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get_akeneo_product/{reference}', function($reference) {
	return new AkeneoProductResource(AkeneoProduct::findOrFail($reference));
});

Route::get('/get_akeneo_products', function() {
	return new AkeneoProductCollection(AkeneoProduct::paginate());
});