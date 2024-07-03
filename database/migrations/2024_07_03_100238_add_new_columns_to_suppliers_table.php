<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {

            $table->string('bank')->nullable()->after('open_balance_date');
            $table->string('bank_code')->nullable()->after('bank');
            $table->string('payment_terms')->nullable()->after('bank_code');
            $table->decimal('credit_limit', 16,4)->after('payment_terms')->default(0);
            $table->string('mpesa_payment')->nullable()->after('credit_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            //
        });
    }
}
