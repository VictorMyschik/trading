<?php

namespace App\Forms\FormBase;

use App\Helpers\System\MrMessageHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class MrForm extends Controller
{
  public static function loadForm(
    string $routeName,
    array $data,
    $btnName = null,
    $btnClass = array(),
    bool $needReload = false,
    string $methodName = ''
  )
  {
    $action = null;
    foreach (Route::getRoutes() as $route)
    {
      if(isset($route->action['as']) && $route->action['as'] === $routeName)
      {
        $action = $route->action['controller'];
      }
    }

    $object = substr($action, 0, strpos($action, '@'));

    return $object::getFormBase($routeName, $data, $btnName, $btnClass, $needReload);
  }
}