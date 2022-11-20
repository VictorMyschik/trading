<?php

namespace App\Classes;

/**
 * Configuration queue settings
 */
class TradingConfigClass
{
  public static function getQueueList(): array
  {
    $list = [];
    for ($i = 0; $i < 20; $i++) {
      $list[] = strtolower($i . '_queue');
    }

    return $list;
  }
}