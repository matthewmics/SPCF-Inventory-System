<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->foreignId('file_storage_id')->nullable()->constrained('file_storages');
            $table->foreignId('transfer_id')->nullable()->constrained('transfer_requests');
            $table->foreignId('repair_id')->nullable()->constrained('repair_requests');
            $table->foreignId('borrow_id')->nullable()->constrained('borrow_request2s');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notes');
    }
};
