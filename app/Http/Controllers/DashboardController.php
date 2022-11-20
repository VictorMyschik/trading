<?php

namespace App\Http\Controllers;

use App\Http\Controllers\TableControllers\MrTradingTableController;
use App\Models\MrTrading;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{
  public function index(): Factory|View|Application
  {
    $out = [];

    $t = new MrTradingTableController();
    $out['trading'] = $t->buildTable()->render();

    return View('dashboard')->with($out);
  }

  public function deleteTrading(int $trading_id): RedirectResponse
  {
    $trading = MrTrading::loadByOrDie($trading_id);
    $trading->delete_mr();

    return back();
  }

  public function restart(): RedirectResponse
  {
    Artisan::call('restart');

    return back();
  }

  public function stop(): RedirectResponse
  {
    Artisan::call('stop');

    return back();
  }
}