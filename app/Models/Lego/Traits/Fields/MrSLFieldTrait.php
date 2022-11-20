<?php

namespace App\Models\Lego\Traits\Fields;

/**
 * Json данные
 */
trait MrSLFieldTrait
{
  public function getSL()
  {
    return $this->SL;
  }

  public function setSL($value): void
  {
    $this->SL = $value;
  }

  public function getJsonField(string $field)
  {
    $data = json_decode($this->getSL(), true);

    return $data[$field] ?? null;
  }

  public function setJsonField(string $field, $value): void
  {
    $data = json_decode($this->getSL(), true);

    if(!$data)
    {
      $data = array();
    }

    if(!$value)
    {
      unset($data[$field]);
    }
    else
    {
      $data[$field] = $value;
    }

    $this->setSL(json_encode($data));
  }
}