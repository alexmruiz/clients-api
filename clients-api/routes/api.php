<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;


//======================= GET =======================
Route::get('/clients', [ClientController::class, 'index']);

Route::get('/clients/{id}', [ClientController::class, 'show']);

//======================= POST ======================
Route::post('/clients', [ClientController::class, 'store']);

//======================= PUT =======================
Route::put('/clients/{id}', [ClientController::class, 'update']);

//======================= DELETE ======================
Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
