<?php

namespace App\Http\Controllers;

use App\Jobs\TradingJob;

class HomeController extends Controller
{
  public function index()
  {
    return view('home');
  }

  public function runTrading()
  {
    $parameters = [
      [
        'stock'    => 'Exmo',
        'diff'     => 0.2,
        'maxTrade' => 200,
        'pair'     => 'SHIB_USD'
      ]
    ];

    foreach($parameters as $parameter) {
      self::tradingByStock($parameter);
    }
  }

  public static function tradingByStock(array $parameter)
  {
    $className = $parameter['stock'] . 'Class';
    $class = "App\\Classes\\" . $className;

    $object = new $class($parameter);
    $object->trade();

    TradingJob::dispatch($parameter);
  }
}
