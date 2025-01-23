<?php

namespace Tests\Feature;

use App\Classes\Client\PayeerClient;
use App\Classes\Client\YobitClient;
use App\Classes\PayeerClass;
use App\Classes\TradeBaseClass;
use App\Classes\Yobit;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testRunJob(): void
    {
        TradeBaseClass::tradingByStock([
            'strategy'    => TradeBaseClass::STRATEGY_BASE,
            'skipSum'     => 15,
            'pair'        => 'XRP_USD',
            'diff'        => 0.45,
            'quantityMax' => 500,
            'quantityMin' => 1,
            'client'      => new YobitClient(),
        ]);
       /* TradeBaseClass::tradingByStock([
            'strategy'    => TradeBaseClass::STRATEGY_BASE,
            'skipSum'     => 5,
            'pair'        => 'DOGE_USDT',
            'diff'        => 0.5,
            'quantityMax' => 50,
            'quantityMin' => 1,
            'client'      => new YobitClient(),
        ]);*/
        }

    public function testPayeer(): void
    {
        $service = new PayeerClass(
            strategy: TradeBaseClass::STRATEGY_BASE,
            skipSum: 25,
            pair: 'GMT_USDT',
            diff: 0.3,
            quantityMax: 100,
            quantityMin: null,
            client: new PayeerClient()
        );

        for ($i = 0; $i < 1000; $i++) {
            $service->trade();
            sleep(10);
        }
    }

    public function testYobit(): void
    {
        $service = new Yobit(
            strategy: TradeBaseClass::STRATEGY_BASE,
            skipSum: 5,
            pair: 'XRP_USD',
            diff: 0.4,
            quantityMax: 500,
            quantityMin: 1,
            client: new YobitClient(),
        );

        $service->trade();
    }
}
