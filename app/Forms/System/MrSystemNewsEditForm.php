<?php

namespace App\Forms\System;

use App\Forms\FormBase\MrFormBase;
use App\Models\MrNews;
use Illuminate\Support\Facades\Auth;

/**
 * Каталог атрибутов
 */
class MrSystemNewsEditForm extends MrFormBase
{
  protected function builderForm(&$form, $args)
  {
    $news = MrNews::loadBy($args['news_id']);

    $form['#title'] = 'Создание нового атрибута';

    $form['Title'] = array(
      '#type'     => 'textfield',
      '#title'    => 'Заголовок',
      '#required' => true,
      '#class'    => ['mr-border-radius-5'],
      '#value'    => $news?->getTitle(),
    );

    $form['Text'] = array(
      '#type'  => 'textarea',
      '#title' => 'Текст',
      '#class' => ['mr-border-radius-5'],
      '#value' => $news?->getText(),
      '#rows'  => 5
    );

    return $form;
  }

  protected function validateForm()
  {
    if(!$this->v['Title']) {
      $out['Title'] = 'Введите заголовок';
    }

    return $out;
  }

  public function submitForm(int $id)
  {
    $this->validateFormBase();

    $news = MrNews::loadBy($id) ?: new MrNews();

    $news->setUserID(Auth::id());
    $news->setTitle($this->v['Title']);
    $news->setText($this->v['Text'] ?: null);

    $news->save_mr();
  }
}