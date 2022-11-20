<?php

namespace App\Models;

use App\Models\Lego\Traits\Fields\MrDescriptionNullableFieldTrait;
use App\Models\Lego\Traits\Fields\MrIsActiveFieldTrait;
use App\Models\Lego\Traits\Fields\MrOrmDateTimeNullableFieldTrait;
use App\Models\Lego\Traits\Fields\MrWriteDateFieldTrait;
use App\Models\ORM\ORM;

class MrTrading extends ORM
{
  use MrDescriptionNullableFieldTrait;
  use MrIsActiveFieldTrait;
  use MrWriteDateFieldTrait;
  use MrOrmDateTimeNullableFieldTrait;

  protected $table = 'mr_trading';
  protected $fillable = [
    'StockID',
    'Different',
    'MaxTrade',
    'Pair',
    'SkipSum',
    'Description',
    'IsActive',
    //'WriteDate'
  ];

  public function getStock(): MrStock
  {
    return MrStock::loadByOrDie($this->StockID);
  }

  public function setStockID(int $value): void
  {
    $this->StockID = $value;
  }

  public function getDifferent(): float
  {
    return $this->Different;
  }

  public function setDifferent(float $value): void
  {
    $this->Different = $value;
  }

  public function getMaxTrade(): float
  {
    return $this->MaxTrade;
  }

  public function setMaxTrade(float $value): void
  {
    $this->MaxTrade = $value;
  }

  public function getPair(): string
  {
    return $this->Pair;
  }

  public function setPair(string $value): void
  {
    $this->Pair = $value;
  }

  public function getSkipSum(): float
  {
    return $this->SkipSum;
  }

  public function setSkipSum(float $value): void
  {
    $this->SkipSum = $value;
  }
}