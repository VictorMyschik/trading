<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMrStockTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('mr_stock', function(Blueprint $table) {
      $table->increments('id');
      $table->string('Name');
      $table->string('Description')->nullable();
      $table->boolean('IsActive')->default(0);
      $table->timestamp('WriteDate')->useCurrent()->useCurrentOnUpdate();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('mr_stock');
  }
}
