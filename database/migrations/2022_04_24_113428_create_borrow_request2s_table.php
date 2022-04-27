<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('borrow_request2s', function (Blueprint $table) {
            $table->id();
            $table->text('purpose');
            $table->text('borrow_details');
            $table->text('rejection_details')->nullable();
            $table->date('from');
            $table->date('to');
            $table->string('borrower');
            $table->string('status')->default('pending');

            $table->foreignId('destination_room')->constrained('rooms');
            $table->foreignId('worker')->nullable()->constrained('users');
            $table->foreignId('requested_by')->constrained('users');

            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('borrow_request2s');
    }
};
