<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Тестовый клас для экспериментов и чернового
 */
class MrTestController extends Controller
{
  public function index()
  {
    \Ratchet\Client\connect('wss://ws-api.exmo.com/v1/public')->then(function ($conn) {
      $exmoApi = new \Exmo\WebSocketApi\Client($conn);
      $exmoApi->subscribe([
        "spot/trades:BTC_USD",
        "spot/ticker:LTC_USD",
      ]);

      $exmoApi->onMessage(function ($data) {
        if ($data['event'] === \Exmo\WebSocketApi\Client::EVENT_ERROR) {
          throw new Exception($data['message'], $data['code']);
        }
        $json = json_encode($data);
        DB::table('table_name')->insert(['column_name' => $json]);
      });

      $conn->on('close', function ($code = null, $reason = null) use ($exmoApi) {
        echo "Connection closed. Code: $code; Reason: {$reason}; Session ID: {$exmoApi->getSessionId()}" . PHP_EOL;
      });

    }, function (\Exception $e) {
      echo "Could not connect: {$e->getMessage()}" . PHP_EOL;
    });
  }
}