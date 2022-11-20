<?php

namespace App\Models\Lego\Traits\Fields;

trait MrDescriptionNullableFieldTrait
{
  public function getDescription(): ?string
  {
    return $this->Description;
  }

  public function setDescription(?string $value): void
  {
    // Пустую строку не храним - сразу null
    if(!$value)
      $value = null;

    $this->Description = $value;
  }
}