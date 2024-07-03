<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {

            $table->string('account_no')->nullable()->after('on_account');
            $table->string('account_name')->nullable()->after('account_no');
            $table->string('bank')->nullable()->after('account_name');
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
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
}
