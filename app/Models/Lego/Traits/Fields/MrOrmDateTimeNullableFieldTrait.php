<?php

namespace App\Models\Lego\Traits\Fields;

use App\Helpers\System\MrDateTime;
use Exception;

trait MrOrmDateTimeNullableFieldTrait
{
  protected function getDateNullableField(string $field): ?MrDateTime
  {
    return $this->$field ? MrDateTime::fromValue($this->$field) : null;
  }

  /**
   * @param $value
   * @param $field
   * @throws Exception
   */
  protected function setDateNullableField($value, $field)
  {
    if ($value)
    {
      if ($value === '0001-01-01T00:00:00')
      {
        $this->$field = null;
        return;
      }

      if ($value instanceof \DateTime)
      {
        $value = new MrDateTime($value->format(MrDateTime::MYSQL_DATETIME));
      }
      else
      {
        $value = MrDateTime::fromValue($value);
      }
      $this->$field = $value;
    }
    else
    {
      $this->$field = null;
    }
  }
}