<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use \App\Models\SoapActionStage;
use \App\Models\SoapActionStatus;

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
            $table->integer('stage')->default(SoapActionStage::STAGE_INITIAL);
            $table->integer('event')->nullable();
            $table->integer('status')->default(SoapActionStatus::STATUS_READY);
            $table->text('data')->nullable();
            $table->text('request')->nullable();
            $table->text('response')->nullable();

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
