<?php

namespace App\Models\Lego\Traits\Fields;

use App\Models\User;

trait MrUserFieldTrait
{
  public function getUser(): User
  {
    return User::find($this->UserID);
  }

  public function setUserID(int $value): void
  {
    $this->UserID = $value;
  }
}