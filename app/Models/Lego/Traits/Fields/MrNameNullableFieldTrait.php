<?php

namespace App\Models\Lego\Traits\Fields;

trait MrNameNullableFieldTrait
{
  public function getName(): ?string
  {
    return $this->Name;
  }

  public function setName(?string $value): void
  {
    $this->Name = $value;
  }
}