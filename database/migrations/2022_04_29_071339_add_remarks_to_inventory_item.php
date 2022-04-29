<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->text('remarks')->nullable();
        });
    }

    public function down()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['remarks']);
        });
    }
};
