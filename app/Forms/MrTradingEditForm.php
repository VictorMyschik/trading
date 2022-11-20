<?php

namespace App\Forms;

use App\Classes\TradeBaseClass;
use App\Forms\FormBase\MrFormBase;
use App\Models\MrStock;
use App\Models\MrTrading;

class MrTradingEditForm extends MrFormBase
{
  protected function builderForm(&$form, $args)
  {
    $trading = MrTrading::loadBy($args['trading_id']);

    $form['#title'] = 'Настройки валютных пар';

    $form['StockID'] = array(
      '#type'          => 'select',
      '#title'         => 'Биржа',
      '#default_value' => $trading ? $trading->getStock()->id() : 0,
      '#value'         => array_merge([0 => 'не выбрано'] + MrStock::getSelectList()),
    );

    $form['Strategy'] = array(
      '#type'          => 'select',
      '#title'         => 'Стратегия',
      '#default_value' => $trading ? $trading->getStrategy() : 0,
      '#value'         => [0 => 'не выбрано'] + TradeBaseClass::getStrategyList(),
    );

    $form['Pair'] = array(
      '#type'  => 'textfield',
      '#title' => 'Валютная пара',
      '#class' => ['mr-border-radius-5'],
      '#value' => $trading?->getPair(),
    );

    $form['Different'] = array(
      '#type'  => 'textfield',
      '#title' => 'Different',
      '#class' => ['mr-border-radius-5'],
      '#value' => $trading?->getDifferent(),
    );

    $form['MaxTrade'] = array(
      '#type'  => 'textfield',
      '#title' => 'Максимальная сумма торговли',
      '#class' => ['mr-border-radius-5'],
      '#value' => $trading?->getMaxTrade(),
    );

    $form['SkipSum'] = array(
      '#type'  => 'textfield',
      '#title' => 'Сумма пропуска',
      '#class' => ['mr-border-radius-5'],
      '#value' => $trading?->getSkipSum(),
    );

    $form['Description'] = array(
      '#type'  => 'textfield',
      '#title' => 'Примечание',
      '#value' => $trading?->getDescription(),
    );

    $form['IsActive'] = array(
      '#type'       => 'checkbox',
      '#title'      => 'Активно',
      '#value'      => true,
      '#attributes' => $trading ? [$trading->isActive() ? 'checked' : ''] : []
    );

    return $form;
  }

  public function submitForm(int $trading_id)
  {
    $this->validateFormBase();

    $trading = MrTrading::loadBy($trading_id) ?: new MrTrading();

    $trading->setStockID($this->v['StockID']);
    $trading->setPair($this->v['Pair']);
    $trading->setDifferent($this->v['Different']);
    $trading->setMaxTrade($this->v['MaxTrade']);
    $trading->setSkipSum($this->v['SkipSum']);
    $trading->setDescription($this->v['Description'] ?: null);
    $trading->setIsActive($this->v['IsActive'] ?? 0);

    $trading->save_mr();
  }
}