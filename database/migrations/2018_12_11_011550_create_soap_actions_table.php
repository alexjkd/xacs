<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSoapActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soap_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stage')->default(0);
            $table->integer('event')->default(0);
            $table->integer('status')->default(0);

            $table->integer('fk_cpe_id')->unsigned();
            $table->foreign('fk_cpe_id')
                ->references('id')->on('cpes')
                ->onDelete('cascade');

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
        Schema::dropIfExists('soap_actions');
    }
}
