<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileStorageToTransferRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_requests', function (Blueprint $table) {
            $table->foreignId('file_storage_id')
                ->nullable()
                ->constrained('file_storages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_requests', function (Blueprint $table) {
            $table->dropColumn('file_storage_id');
        });
    }
}
