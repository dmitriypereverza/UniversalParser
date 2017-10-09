<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->increments('id');
            $table->text('url');
            $table->string('title')->nullable();
            $table->string('text')->nullable();
            $table->string('server_response_code')->nullable();
            $table->boolean('is_viewed')->nullable();
            $table->integer('depth')->nullable();
            $table->timestamps();
        });

        Schema::create('links_ref', function (Blueprint $table) {
            $table->integer('parent_id')->unsigned()->index();
            $table->foreign('parent_id')->references('id')->on('links')->onDelete('cascade');
            $table->integer('child_id')->unsigned()->index();
            $table->foreign('child_id')->references('id')->on('links')->onDelete('cascade');

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
        Schema::dropIfExists('links_ref');
        Schema::dropIfExists('links');
    }
}
