<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_building', function (Blueprint $table) {
            $table->foreignId('building_id')->constrained('buildings');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->primary(['user_id', 'building_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('department_buildings');
    }
}
