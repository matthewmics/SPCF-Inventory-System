<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('purchase_item_requests', function (Blueprint $table) {
            $table->id();
            $table->string('requestor');
            $table->string('department');
            $table->string('to_purchase');
            $table->text('purpose');
            $table->string('item_type')->default('PC');
            $table->foreignId('attached_file_id')->nullable()->constrained('file_storages')->cascadeOnDelete();
            $table->foreignId('destination_room')->constrained('rooms');

            $table->string('status')->default('pending');
            $table->text('rejection_details')->nullable();
            
            $table->foreignId('worker')->nullable()->constrained('users');
            $table->foreignId('requested_by')->constrained('users');

            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('purchase_item_requests');
    }
};
