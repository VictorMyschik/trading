<?php

use App\Classes\ExmoClass;
use Tests\TestCase;

class DebugTest extends TestCase
{
  public function testDebugFlow()
  {
    $data = [
      'skipSum'     => 50,
      'pair'        => 'SHIB_USDT',
      'diff'        => 0.8,
      'maxTrade' => 100,
    ];

    $object = new ExmoClass($data);
    $object->trade();
  }
}