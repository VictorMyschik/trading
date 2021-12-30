<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function() {
  return view('welcome');
});

Auth::routes();

Route::group(['middleware' => ['auth']], function() {
  Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
  Route::get('/trading', [App\Http\Controllers\HomeController::class, 'runTrading'])->name('home');
});

Route::get('/clear', function() {
  Artisan::call('cache:clear');
  Artisan::call('view:clear');
  Artisan::call('route:clear');
  //DB::table('trade_logs')->truncate();

  //composer dump-autoload --optimize
  return back();
})->name('clear');