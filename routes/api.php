<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MqttController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('mqtt-test')->group(function () {
    Route::post('publish_message', [MqttController::class,'sendMessage']);
    Route::post('start_listening', [MqttController::class,'listenToMessages']); 
     
    // Route::post('users/{id}', function ($id) {
        
    // });
});
