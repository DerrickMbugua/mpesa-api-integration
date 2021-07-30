<?php

use App\Http\Controllers\MpesaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('v1/access-token', [MpesaController::class, "generateAccessToken"]);
Route::post('v1/token', [MpesaController::class, "token"]);
Route::post('v1/stkpush', [MpesaController::class, "stkPush"]);
Route::post('v1/validation', [MpesaController::class, "mpesaValidation"]);
Route::post('v1/confirmation', [MpesaController::class, "mpesaConfirmation"]);
Route::post('v1/register/url', [MpesaController::class, "mpesaRegisterUrls"]);
Route::post('v1/register-url', [MpesaController::class, "registerUrls"]);
Route::post('v1/b2c', [MpesaController::class, "b2cRequest"]);
