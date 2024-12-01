<?php

use App\Jobs\TradingJob;
use App\Models\MrTrading;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Tests\TestCase;

class DebugTest extends TestCase
{
    public function testDebugFlow()
    {
        foreach (MrTrading::all() as $item) {
            if (!$item->isActive()) {
                continue;
            }

            $parameter = [
                'strategy'  => $item->getStrategy(),
                'stock'     => $item->getStock()->getName(),
                'diff'      => $item->getDifferent(),
                'maxTrade'  => $item->getMaxTrade(),
                'pair'      => strtoupper($item->getPair()),
                'queueName' => strtolower($item->id() . '_queue'),
                'skipSum'   => $item->getSkipSum(),
            ];

            self::tradingByStock($parameter);
        }
    }

    public static function tradingByStock(array $parameter): void
    {
        TradingJob::dispatch($parameter);
    }

    public function testSocket()
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
