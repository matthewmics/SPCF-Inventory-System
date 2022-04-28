<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('borrow_request2s', function (Blueprint $table) {
            $table->text('borrow_data')->nullable();
        });
    }

    public function down()
    {
        Schema::table('borrow_request2s', function (Blueprint $table) {
            $table->dropColumn(['borrow_data']);
        });
    }
};
