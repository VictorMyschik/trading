<?php

namespace App\Http\Controllers;

use App\Jobs\TradingJob;

class HomeController extends Controller
{
  public function index()
  {
    return view('home');
  }

  const COUNT_R = 5;
}
