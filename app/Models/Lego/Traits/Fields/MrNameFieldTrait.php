<?php

namespace App\Models\Lego\Traits\Fields;

trait MrNameFieldTrait
{
  public function getName(): string
  {
    return $this->Name;
  }

  public function setName(string $value): void
  {
    $this->Name = $value;
  }
}