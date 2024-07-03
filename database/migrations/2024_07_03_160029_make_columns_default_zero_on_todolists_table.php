<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnsDefaultZeroOnTodolistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('todolists', function (Blueprint $table) {

            $table->decimal('task_completion', 16, 2)->default(0.00)->change();
            $table->decimal('task_expected_percent', 16, 2)->default(0.00)->change();
            $table->decimal('task_percent_of_expected', 16, 2)->default(0.00)->change();
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
