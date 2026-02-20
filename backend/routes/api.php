<?php

use App\Http\Controllers\InspectionController;
use App\Http\Controllers\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::get('/master-data', [MasterDataController::class, 'index']);

Route::get('/inspections',  [InspectionController::class, 'index']);
Route::post('/inspections', [InspectionController::class, 'store']);
