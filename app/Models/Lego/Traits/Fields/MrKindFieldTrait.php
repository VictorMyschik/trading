<?php

namespace App\Models\Lego\Traits\Fields;

trait MrKindFieldTrait
{
  public function getKind(): int
  {
    return $this->Kind;
  }

  public function setKind(int $value): void
  {
    abort_if(!isset(self::getKindList()[$value]), 500, 'Unknown kind');

    $this->Kind = $value;
  }

  public function getKindName(): string
  {
    return self::getKindList()[$this->getKind()];
  }
}