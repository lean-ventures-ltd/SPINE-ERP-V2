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

            $table->unsignedDecimal('task_completion')->default(0.00)->change();
            $table->unsignedDecimal('task_expected_percent')->default(0.00)->change();
            $table->unsignedDecimal('task_percent_of_expected')->default(0.00)->change();
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
