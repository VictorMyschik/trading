<?php

namespace App\Http\Controllers;

use App\Models\Good\MrGood;

/**
 * Тестовый клас для экспериментов и чернового
 */
class MrTestController extends Controller
{
  public function index()
  {
    $good = MrGood::loadByOrDie(1);

     dd($good->getFullData());
  }
}