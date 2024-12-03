<?php

namespace Tests\Feature;

use App\Classes\Client\PayeerClient;
use App\Classes\PayeerClass;
use App\Classes\TradeBaseClass;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_example()
    {
        $service = new PayeerClass(
            strategy: TradeBaseClass::STRATEGY_BASE,
            skipSum: 25,
            pair: 'XRP_USD',
            diff: 0.3,
            quantityMax: 25,
        );

        for ($i = 0; $i < 1000; $i++) {
            $service->trade();
           //sleep(1);
        }
    }

    public function testPayeerClient(): void
    {
        $client = new PayeerClient();
        $client->getBalance();
    }
}
