<?php

use App\Forms\MrTradingEditForm;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function() {
  return view('welcome');
});

Auth::routes();

Route::get('/test', [App\Http\Controllers\MrTestController::class, 'test'])->name('test_page');

Route::group(['middleware' => ['auth']], function() {
  Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

  Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard_page');

  Route::match(['get', 'post'], '/trading/{trading_id}/submit', [MrTradingEditForm::class, 'submitForm'])->name('trading_form_submit');
  Route::match(['get', 'post'], '/trading/{trading_id}/edit', [MrTradingEditForm::class, 'getFormBuilder'])->name('trading_form_edit');
  // Delete pair
  Route::get('/trading/{trading_id}/delete', [App\Http\Controllers\DashboardController::class, 'deleteTrading'])->name('delete_trading');
  // restart
  Route::get('/trading/restart', [App\Http\Controllers\DashboardController::class, 'restart'])->name('trading_restart');
  Route::get('/trading/stop', [App\Http\Controllers\DashboardController::class, 'stop'])->name('trading_stop');
});

Route::get('/clear', function() {
  Artisan::call('cache:clear');
  Artisan::call('view:clear');
  Artisan::call('route:clear');
  //composer dump-autoload --optimize
  return back();
})->name('clear');
