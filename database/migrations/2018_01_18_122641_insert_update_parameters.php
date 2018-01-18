    <?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertUpdateParameters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tmp_search_results', function (Blueprint $table) {
            $table->string('config_site_name')->nullable()->change();
            $table->string('id_session')->nullable()->change();
            $table->text('content')->nullable()->change();
            $table->string('hash')->nullable()->change();
            $table->integer('need_delete')->nullable();
            $table->integer('need_update')->nullable();
            $table->boolean('old_content')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
