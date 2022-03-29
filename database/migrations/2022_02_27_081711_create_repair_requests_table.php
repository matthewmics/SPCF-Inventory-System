<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepairRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requestor_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('handler_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('file_storage_id')
                ->nullable()
                ->constrained('file_storages')
                ->onDelete('cascade');
            $table->string('item_type')->default('PC');
            $table->string('status')->default('pending');
            $table->text('details');
            $table->text('rejection_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repair_requests');
    }
}
