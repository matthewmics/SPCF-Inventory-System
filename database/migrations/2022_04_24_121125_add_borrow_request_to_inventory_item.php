<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('borrow_request_id')->nullable()->constrained('borrow_request2s')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['borrow_request_id']);
            $table->dropColumn(['borrow_request_id']);
        });
    }
};
