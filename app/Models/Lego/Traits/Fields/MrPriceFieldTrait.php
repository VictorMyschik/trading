<?php

namespace App\Models\Lego\Traits\Fields;

trait MrPriceFieldTrait
{
  public function getPrice(): float
  {
    return $this->Price;
  }

  public function setPrice(float $value): void
  {
    $this->Price = $value;
  }
}