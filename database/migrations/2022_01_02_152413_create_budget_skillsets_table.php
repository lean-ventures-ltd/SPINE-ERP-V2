<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetSkillsetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budget_skillsets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('budget_id')->unsigned();
            $table->text('skill');
            $table->bigInteger('charge');
            $table->integer('hours');
            $table->integer('no_technician');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            // foreign key
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budget_skillsets');
    }
}
