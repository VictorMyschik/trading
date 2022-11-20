<?php

namespace App\Models;

use App\Helpers\System\MrCacheHelper;
use App\Models\Lego\Traits\Fields\MrDescriptionNullableFieldTrait;
use App\Models\Lego\Traits\Fields\MrIsActiveFieldTrait;
use App\Models\Lego\Traits\Fields\MrNameFieldTrait;
use App\Models\Lego\Traits\Fields\MrOrmDateTimeNullableFieldTrait;
use App\Models\Lego\Traits\Fields\MrWriteDateFieldTrait;
use App\Models\ORM\ORM;

class MrStock extends ORM
{
  use MrIsActiveFieldTrait;
  use MrNameFieldTrait;
  use MrWriteDateFieldTrait;
  use MrDescriptionNullableFieldTrait;
  use MrOrmDateTimeNullableFieldTrait;

  protected $table = 'mr_stock';
  protected $fillable = [
    'Name',
    'Description',
    'IsActive',
    //'WriteDate'
  ];

  public static function getSelectList(): array
  {
    return MrCacheHelper::getCachedData('stockSelectList2', function() {
      $out = array();

      foreach(self::all() as $item) {
        $out[$item->id()] = $item->getName();
      }

      return $out;
    });
  }
}