<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_models', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('brand_id');
            $table->integer('model_id');
            $table->integer('body_id');
            $table->integer('generation_id');
            $table->integer('engine_id');
            $table->string('parse_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_models');
    }
}
