<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('borrow_request2s', function (Blueprint $table) {
            $table->foreignId('borrower_file')->nullable()->constrained('file_storages')->onDelete('SET NULL');
            $table->text('borrower_note')->nullable();
        });
    }

    public function down()
    {
        Schema::table('borrow_request2s', function (Blueprint $table) {
            $table->dropForeign(['borrower_file']);
            $table->dropColumn(['borrower_note', 'borrower_file']);
        });
    }
};
