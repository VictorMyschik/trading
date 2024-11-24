<?php

namespace App\Http\Controllers;

use App\Classes\ExmoPairsDiff;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Тестовый клас для экспериментов и чернового
 */
class MrTestController extends Controller
{
    public function index()
    {
        $service = new ExmoPairsDiff();
        $result = $service->getData();

        return View('statistic')->with(['list' => $result]);
    }
}
