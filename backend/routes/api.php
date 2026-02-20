<?php

use App\Http\Controllers\InspectionController;
use App\Http\Controllers\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::get('/master-data', [MasterDataController::class, 'index']);

Route::get('/inspections',              [InspectionController::class, 'index']);
Route::post('/inspections',             [InspectionController::class, 'store']);
Route::get('/inspections/{id}',         [InspectionController::class, 'show']);
Route::patch('/inspections/{id}/status',[InspectionController::class, 'updateStatus']);
Route::patch('/inspections/{id}',       [InspectionController::class, 'updateHeader']);
Route::patch('/inspections/{id}/items/{itemId}',                     [InspectionController::class, 'updateItem']);
Route::patch('/inspections/{id}/items/{itemId}/lots/{lotId}',        [InspectionController::class, 'updateLot']);
