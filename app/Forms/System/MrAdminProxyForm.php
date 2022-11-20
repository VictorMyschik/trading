<?php

namespace App\Forms\System;

use App\Forms\FormBase\MrFormBase;
use App\Models\System\MrProxyServer;
use Illuminate\Http\Request;

class MrAdminProxyForm extends MrFormBase
{
  protected function builderForm(&$form, $args)
  {
    $proxy = MrProxyServer::loadBy($args['proxy_id']);

    $form['Address'] = array(
      '#type'  => 'textfield',
      '#title' => 'Адрес с портом',
      '#class' => ['mr-border-radius-5'],
      '#value' => $proxy?->getAddress(),
    );

    $form['last_is_active'] = array(
      '#type'       => 'checkbox',
      '#title'      => 'Активность последний раз',
      '#value'      => true,
      '#attributes' => $proxy ? [$proxy->isActive() ? 'checked' : ''] : []
    );

    $form['is_active'] = array(
      '#type'       => 'checkbox',
      '#title'      => 'Активно',
      '#value'      => true,
      '#attributes' => $proxy ? [$proxy->isActive() ? 'checked' : ''] : []
    );

    $form['Description'] = array(
      '#type'  => 'textarea',
      '#title' => 'Примечание',
      '#class' => ['mr-border-radius-5'],
      '#value' => $proxy?->getDescription(),
      '#rows'  => '3',
    );


    return $form;
  }

  public function submitForm(int $id)
  {
    $this->validateFormBase();

    $translate = MrProxyServer::loadBy($id) ?: new MrProxyServer();
    $translate->setAddress($this->v['Address']);
    $translate->setIsActive($this->v['is_active']??false);
    $translate->setLastIsActive($this->v['last_is_active']??false);
    $translate->setDescription($this->v['Description']);

    $translate->save_mr();
  }
}