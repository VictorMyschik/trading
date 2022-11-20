<?php

use App\Http\Controllers\MrSendController;
use App\Http\Controllers\TableControllers\BaseTableController\BaseTableController;
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
// генератор URL для таблиц
Route::match(['get', 'post'], '/table', [BaseTableController::class, 'getTableClass'])->name('base_table');
/// Атрибуты в товаре
// Удалить атрибут из товара
Route::match(['get', 'post'], '/form', [MrSendController::class, 'sendAction']);
