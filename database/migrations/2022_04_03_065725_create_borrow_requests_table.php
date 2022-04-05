<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handler_user_id')->nullable()->constrained('users');
            $table->foreignId('requestor_user_id')->constrained('users');
            $table->foreignId('current_room_id')->constrained('rooms');
            $table->foreignId('destination_room_id')->constrained('rooms');
            $table->foreignId('item_id')->constrained('inventory_items');
            $table->string('details');
            $table->string('rejection_details')->nullable();
            $table->string('item_type');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('borrow_requests');
    }
};
