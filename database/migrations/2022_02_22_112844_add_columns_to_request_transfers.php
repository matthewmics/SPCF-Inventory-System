<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToRequestTransfers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_requests', function (Blueprint $table) {
            $table->foreignId('handler_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            $table->text('rejection_details')->nullable();
            $table->string('item_type', 100)->default('PC');
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
            $table->dropColumn(['handler_user_id', 'rejection_details', 'item_type']);
        });
    }
}
