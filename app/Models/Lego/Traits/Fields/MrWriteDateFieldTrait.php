<?php

namespace App\Models\Lego\Traits\Fields;

use App\Helpers\System\MrDateTime;

trait MrWriteDateFieldTrait
{
  public function getWriteDate(): MrDateTime
  {
    return $this->getDateNullableField('WriteDate');
  }

  public function GetWriteShortDate(): string
  {
    return $this->getWriteDate()->getShortDate();
  }

  public function GetWriteShortDateTitleSortTime(): string
  {
    return $this->getWriteDate()->getShortDateTitleShortTime();
  }

  public function GetWriteShortDateFullTime(): string
  {
    return $this->getWriteDate()->getShortDateFullTime();
  }

  public function GetWriteShortDateFullTime2Lines(): string
  {
    $r = "<div>{$this->getWriteDate()->getShortDate()}</div>";
    $r .= "<div>{$this->getWriteDate()->getShortTime()}</div>";
    return $r;
  }
}