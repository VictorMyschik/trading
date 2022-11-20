<?php

namespace App\Forms\FormBase;

use App\Exceptions\Handler;
use App\Helpers\System\MtFloatHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class MrFormBase extends Controller
{
  protected array $v = array();
  protected array $errors = [];

  /**
   * Построение кнопки вызова формы
   *
   * @param string $routeName
   * @param array $data //параметры для маршрута
   * @param string|null $btnName
   * @param array $btnClass
   * @param bool $needReload
   * @param string $methodName
   * @return string
   */
  public static function getFormBase(
    string  $routeName,
    array   $data = [],
    ?string $btnName = null,
    array   $btnClass = array(),
    bool    $needReload = false,
    string  $methodName = ''
  ): string
  {
    $out = array();
    $out['needReload'] = $needReload;
    $out['methodName'] = $methodName;
    $out['url'] = route($routeName, $data);
    $out['btnName'] = $btnName ?? 'Изменить';
    $baseClassBtn = array(
      'btn', 'btn-sm', 'fa'
    );

    foreach($btnClass as $class)
    {
      $baseClassBtn[] = $class;
    }

    $baseClassBtn[] = 'mr-border-radius-5';

    $out['btnClass'] = $baseClassBtn;

    return View('Form.button_form_base')->with($out)->toHtml();
  }

  public function getFormBuilder()
  {
    $route_parameters = Route::getFacadeRoot()->current()->parameters();

    $form = array(
      '#title' => '',
      '#size'  => $this->size ?? 'w-50',
      '#url'   => ''
    );

    $this->builderForm($form, $route_parameters);

    if(!isset($form['#btn_info']))
    {
      // Получение Rout для сохранения
      $route_referer_name = Route::getFacadeRoot()->current()->action['as'];

      $route_submit = explode('_', $route_referer_name);
      $route_submit[count($route_submit) - 1] = 'submit';
      $form['#url'] = route(implode('_', $route_submit), $route_parameters);
    }

    $formDisplay = View('Form.BaseForm.form_templates')->with(['form' => $form])->toHtml();

    if(request()->getMethod() === 'POST')
    {
      return array(
        'html'      => $formDisplay,
        'form_data' => $form,
      );
    }
    else // GET запрос для дебага
    {
      return View('Form.BaseForm.form_render')->with([
        'form'      => $formDisplay,
        'form_data' => $form,
      ]);
    }
  }

  #region Validation

  /**
   * Базовая валидация на заполненность и тип данных
   * @throws Exception
   */
  protected function validateFormBase(array $additionalInput = [])
  {
    $v = $additionalInput;
    $fields = $this->getFormBuilder()['form_data'];

    foreach($fields as $fieldName => $parameters)
    {
      if(str_starts_with($fieldName, '#')) // Пропуск общих данных
        continue;

      // значение
      $value = request()->get($fieldName, null);

      $title = $parameters['#title'] ?? '';

      if(isset($parameters['#require']) && $parameters['#require'] === true)
      {
        if(empty($value))
        {
          $this->errors[$fieldName] = 'Поле "' . $title . '" должно быть заполнено';
        }
      }

      $this->returnError();

      if($value)
      {
        // Type numeric
        if($parameters['#type'] === 'numeric')
        {
          if(MtFloatHelper::canConvert($value))
          {
            $v[$fieldName] = MtFloatHelper::toFloat($value);
          }
          else
          {
            $this->errors[$fieldName] = 'Поле "' . $parameters['name'] . '" имеет не верный формат';
            $this->returnError();
          }
        }
        elseif($parameters['#type'] === 'textfield')
        {
          if(isset($parameters['#max']) && (int)$parameters['#max'] > strlen($value))
          {
            $this->errors[$fieldName] = 'Поле "' . $parameters['name'] . '" ограничено ' . $parameters['#max'] . ' символов';
          }
        }
      }


      $v[$fieldName] = $value;
    }

    $this->v = $v;

    $this->returnError();

    if(method_exists($this, 'validateForm'))
    {
      $this->validateForm();

      $this->returnError();
    }
  }

  private function returnError()
  {
    abort_if(count($this->errors), Response::HTTP_UNPROCESSABLE_ENTITY, json_encode($this->errors));
  }
  #endregion
}