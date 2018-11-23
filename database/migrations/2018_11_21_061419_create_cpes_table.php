<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCPEsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ConnectionRequestUser')->unique();
            $table->string('ConnectionRequestPassword');
            $table->string('ConnectionRequestURL')->nullable();
            $table->string('Manufacturer')->nullable();
            $table->string('OUI')->nullable();
            $table->string('ProductClass')->nullable();
            $table->string('SerialNumber')->nullable();
            $table->string('HardwareVersion')->nullable();
            $table->string('SoftwareVersion')->nullable();
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
        Schema::dropIfExists('cpes');
    }
}
