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
        'stock'     => 'Exmo',
        'diff'      => 1,
        'maxTrade'  => 125,
        'pair'      => 'MNC_USD',
        'queueName' => 'mnc_usd_queue',
        'skipSum'   => 20,
      ],
      [
        'stock'     => 'Exmo',
        'diff'      => 1,
        'maxTrade'  => 125,
        'pair'      => 'SHIB_USDT',
        'queueName' => 'shib_usdt_queue',
        'skipSum'   => 20,
      ],
      [
        'stock'     => 'Exmo',
        'diff'      => 1,
        'maxTrade'  => 125000,
        'pair'      => 'SMART_RUB',
        'queueName' => 'smart_rub_queue',
        'skipSum'   => 500,
      ],
    ];

    foreach($parameters as $parameter) {
      self::tradingByStock($parameter);
    }
  }

  public static function tradingByStock(array $parameter)
  {
    TradingJob::dispatch($parameter);
  }
}
