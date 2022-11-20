<?php

namespace App\Http\Controllers\TableControllers;

use App\Forms\FormBase\MrForm;
use App\Helpers\System\MrLink;
use App\Http\Controllers\TableControllers\BaseTableController\BaseTableController;
use App\Models\MrTrading;
use Illuminate\Support\Facades\DB;

class MrTradingTableController extends BaseTableController
{
  public static function GetQuery(array $filter, array $args = array())
  {
    return DB::table(MrTrading::getTableName())->select('id');
  }

  public static function getHeader(): array
  {
    return [
      ['name' => 'Склад'],
      ['name' => 'Пара'],
      ['name' => 'Diff'],
      ['name' => 'Skip'],
      ['name' => 'MaxTrade'],
      ['name' => 'Примечание'],
      ['name' => 'Активно'],
      ['name' => 'Обновлено'],
      ['name' => '#'],
    ];
  }

  protected static function buildRow(int $id): array
  {
    $row = array();

    $trading = MrTrading::loadBy($id);

    $row[] = $trading->getStock()->getName();
    $row[] = $trading->getPair();
    $row[] = $trading->getDifferent();
    $row[] = $trading->getSkipSum();
    $row[] = $trading->getMaxTrade();
    $row[] = $trading->getDescription();
    $row[] = $trading->GetWriteShortDateTitleSortTime();
    $row[] = $trading->IsActiveOut();

    $row[] = [
      MrForm::loadForm('trading_form_edit', ['trading_id' => $trading->id()], '', ['btn mr-btn-primary btn-sm fa fa-edit'], true),
      MrLink::AddDangerBtn('delete_trading', ['trading_id' => $trading->id()], '', 'm-l-5 fa-trash-alt', 'Удалить'),
    ];

    return $row;
  }
}