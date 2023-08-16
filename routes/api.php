<?php

use App\Http\Controllers\ColumnController;
use App\Http\Controllers\DocTypeController;
use App\Http\Controllers\DocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::resource('doctype', DocTypeController::class);
Route::resource('column', ColumnController::class);
Route::resource('document', DocumentController::class);
Route::get('document/{id}/download', [DocumentController::class, 'download']);
