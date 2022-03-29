<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoomIdColumnToInventoryItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->constrained('rooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { 
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->removeColumn(['room_id']);
        });
    }
}
