<?php

use App\Models\PurchaseOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            Schema::dropIfExists('purchase_orders');
            Schema::create('purchase_orders', function (Blueprint $table) {

                $table->id();

                $table->foreignId('job_order_id')->nullable()->constrained('job_orders')->onDelete('cascade');
                $table->foreignId('purchase_item_request_id')->nullable()->constrained('purchase_item_requests')->onDelete('cascade');

                $table->string('room_name')->default('Room X');
                $table->string('item_name')->default('Item X');

                $table->foreignId('file_storage_id')->nullable()->constrained('file_storages')->onDelete('cascade');
                $table->timestamps();
                
            });
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            Schema::dropIfExists('purchase_orders');
            Schema::create('purchase_orders', function (Blueprint $table) {

                $table->id();

                $table->foreignId('job_order_id')->nullable()->constrained('job_orders')->onDelete('cascade');
                $table->foreignId('purchase_item_request_id')->nullable()->constrained('purchase_item_requests')->onDelete('cascade');

                $table->string('room_name')->default('Room X');
                $table->string('item_name')->default('Item X');

                $table->foreignId('file_storage_id')->nullable()->constrained('file_storages')->onDelete('cascade');
                $table->timestamps();
                
            });
        });
    }
};
