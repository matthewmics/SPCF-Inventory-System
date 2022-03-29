<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDisposedColumnToInventoryItem extends Migration
{
    
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->boolean('is_disposed')->default(false);
        });
    }

    
    public function down()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['is_disposed']);
        });
    }
}
