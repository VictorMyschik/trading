<?php

namespace App\Models\Lego\Traits\Fields;

use App\Helpers\System\MrBaseHelper;

trait MrIsActiveFieldTrait
{
  public function isActive(): bool
  {
    return $this->IsActive;
  }

  public function setIsActive(bool $value): void
  {
    $this->IsActive = $value;
  }

  public function IsActiveOut(): string
  {
    return MrBaseHelper::getBoolValueDisplay($this->isActive());
  }
}