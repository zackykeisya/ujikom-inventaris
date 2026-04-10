<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLendingDateToDatetime extends Migration
{
    public function up()
    {
        Schema::table('lendings', function (Blueprint $table) {
            $table->dateTime('lending_date')->change();
            $table->dateTime('return_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('lendings', function (Blueprint $table) {
            $table->date('lending_date')->change();
            $table->date('return_date')->nullable()->change();
        });
    }
}