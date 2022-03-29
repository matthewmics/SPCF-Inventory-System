<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requestor_user_id')->constrained('users');
            $table->foreignId('current_room_id')->nullable()->constrained('rooms');            
            $table->foreignId('destination_room_id')->constrained('rooms');    
            $table->foreignId('item_id')->constrained('inventory_items');
            $table->text('details');
            $table->string('status', 100)->default('pending');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer_requests');
    }
}
