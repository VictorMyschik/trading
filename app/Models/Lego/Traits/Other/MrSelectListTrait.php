<?php

namespace App\Models\Lego\Traits\Other;

/**
 * @method static all()
 */
trait MrSelectListTrait
{
  public static function getSelectList(): array
  {
    $out = array();
    foreach (self::all() as $item)
    {
      $out[$item->id()] = $item->getName();
    }
    return $out;
  }
}