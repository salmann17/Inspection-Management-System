<?php

use App\Http\Controllers\InspectionController;
use Illuminate\Support\Facades\Route;

Route::get('/inspections', [InspectionController::class, 'index']);
