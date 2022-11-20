<?php

use App\Classes\ExmoClass;
use App\Models\MrTrading;
use Tests\TestCase;

class DebugTest extends TestCase
{
  public function testDebugFlow()
  {
    $item = MrTrading::where('Pair', 'SHIB_USDT')->first();

    $parameters = [
      'strategy'  => $item->getStrategy(),
      'stock'     => $item->getStock()->getName(),
      'diff'      => $item->getDifferent(),
      'maxTrade'  => $item->getMaxTrade(),
      'pair'      => strtoupper($item->getPair()),
      'queueName' => strtolower($item->id() . '_queue'),
      'skipSum'   => $item->getSkipSum(),
    ];

    $object = new ExmoClass($parameters);
    $object->trade();
  }
}