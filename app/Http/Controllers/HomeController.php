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
        'diff'     => 0.8,
        'maxTrade' => 25,
        'pair'     => 'SHIB_USDT'
      ],
      [
        'stock'    => 'Exmo',
        'diff'     => 0.8,
        'maxTrade' => 25,
        'pair'     => 'MNC_USD'
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
